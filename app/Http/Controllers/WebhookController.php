<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use App\Services\BinancePayService;
use App\Services\FlexPayService;
use App\Services\MLM\MonthlyCommissionService;
use App\Services\PaymentService;
use App\Services\MobileMoneyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected MonthlyCommissionService $commissionService;
    protected PaymentService $paymentService;
    protected MobileMoneyService $mobileMoneyService;
    protected FlexPayService $flexPayService;
    protected BinancePayService $binancePayService;

    public function __construct(
        MonthlyCommissionService $commissionService,
        PaymentService $paymentService,
        MobileMoneyService $mobileMoneyService,
        FlexPayService $flexPayService,
        BinancePayService $binancePayService
    ) {
        $this->commissionService = $commissionService;
        $this->paymentService = $paymentService;
        $this->mobileMoneyService = $mobileMoneyService;
        $this->flexPayService = $flexPayService;
        $this->binancePayService = $binancePayService;
    }

    /**
     * Webhook FlexPay (Orange Money, Airtel Money, M-Pesa).
     * Pas de middleware HMAC générique : validation via SDK handleCallback (+ check statut).
     * Crédit idempotent dans FlexPayService::processCallback.
     */
    public function flexpay(Request $request)
    {
        $data = $request->all();

        Log::info('FlexPay Webhook received', [
            'orderNumber' => $data['orderNumber'] ?? null,
            'reference' => $data['reference'] ?? null,
            'code' => $data['code'] ?? null,
        ]);

        try {
            $result = $this->flexPayService->processCallback($data);

            if (($result['already_processed'] ?? false) === false
                && ($result['status'] ?? null) === 'completed'
                && ! empty($result['transaction_id'])
            ) {
                $eventId = (string) ($data['reference'] ?? $data['orderNumber'] ?? $result['transaction_id']);
                $this->markWebhookProcessed('flexpay', $eventId);
            }

            return response()->json([
                'success' => (bool) ($result['success'] ?? false),
                'message' => $result['message'] ?? 'Webhook reçu',
                'status' => $result['status'] ?? null,
                'already_processed' => (bool) ($result['already_processed'] ?? false),
                'transaction_id' => $result['transaction_id'] ?? null,
            ], (int) ($result['http_status'] ?? 200));
        } catch (\Throwable $e) {
            Log::error('FlexPay Webhook Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook Binance Pay (signature via middleware webhook.binance).
     */
    public function binancepay(Request $request)
    {
        Log::info('Binance Pay webhook received', [
            'bizStatus' => $request->input('bizStatus'),
            'bizType' => $request->input('bizType'),
        ]);

        try {
            $result = $this->binancePayService->processCallback($request);

            return response()->json([
                'returnCode' => $result['returnCode'] ?? 'FAIL',
                'returnMessage' => $result['returnMessage'] ?? null,
            ], (int) ($result['http_status'] ?? 200));
        } catch (\Throwable $e) {
            Log::error('Binance Pay Webhook Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'returnCode' => 'FAIL',
                'returnMessage' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Webhook Crypto (Coinbase) — DEPRECATED: Coinbase Commerce ferme en Mars 2026.
     */
    public function crypto(Request $request)
    {
        Log::info('Crypto webhook received', $request->all());

        $data = $this->validateCryptoRequest($request);

        try {
            DB::beginTransaction();

            $user = User::find($data['user_id']);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $wallet = $user->wallet;
            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            $existing = Transaction::where('reference', $data['transaction_id'])->first();
            if ($existing) {
                return response()->json(['message' => 'Transaction already processed'], 200);
            }

            if ($data['status'] === 'confirmed' || $data['status'] === 'completed') {
                $balanceBefore = $wallet->balance;
                $wallet->balance += $data['amount'];
                $wallet->total_deposited += $data['amount'];
                $wallet->save();

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'type' => 'deposit',
                    'amount' => $data['amount'],
                    'fee' => 0,
                    'net_amount' => $data['amount'],
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'status' => 'completed',
                    'reference' => $data['transaction_id'],
                    'description' => "Crypto deposit {$data['currency']} on {$data['network']}",
                    'metadata' => json_encode([
                        'tx_hash' => $data['tx_hash'] ?? null,
                        'network' => $data['network'],
                        'currency' => $data['currency'],
                    ]),
                    'completed_at' => now(),
                ]);

                if (isset($data['order_id'])) {
                    $this->processOrderPayment($user, $data['order_id']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Crypto webhook processed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing crypto webhook', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook Mobile Money (Legacy - à garder pour compatibilité)
     */
    public function mobileMoney(Request $request)
    {
        Log::info('Mobile Money webhook received', $request->all());

        $data = $this->validateMobileMoneyRequest($request);

        try {
            DB::beginTransaction();

            $user = User::find($data['user_id']);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $wallet = $user->wallet;
            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            $existing = Transaction::where('reference', $data['transaction_id'])->first();
            if ($existing) {
                return response()->json(['message' => 'Transaction already processed'], 200);
            }

            if ($data['status'] === 'success') {
                $balanceBefore = $wallet->balance;
                $wallet->balance += $data['amount'];
                $wallet->total_deposited += $data['amount'];
                $wallet->save();

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'type' => 'deposit',
                    'amount' => $data['amount'],
                    'fee' => 0,
                    'net_amount' => $data['amount'],
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'status' => 'completed',
                    'reference' => $data['transaction_id'],
                    'description' => "Mobile Money deposit {$data['provider']}",
                    'metadata' => json_encode([
                        'provider' => $data['provider'],
                        'phone_number' => $data['phone_number'],
                    ]),
                    'completed_at' => now(),
                ]);

                if (isset($data['order_id'])) {
                    $this->processOrderPayment($user, $data['order_id']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mobile Money webhook processed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing mobile money webhook', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook Générique (pour compatibilité)
     */
    public function payment(Request $request)
    {
        Log::info('Payment webhook received', $request->all());

        $data = $request->validate([
            'transaction_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'status' => 'required|in:completed,pending,failed',
            'payment_method' => 'required|string',
            'order_id' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $user = User::find($data['user_id']);
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            $existing = Transaction::where('reference', $data['transaction_id'])->first();
            if ($existing) {
                return response()->json(['message' => 'Transaction already processed'], 200);
            }

            if ($data['status'] === 'completed') {
                $balanceBefore = $wallet->balance;
                $wallet->balance += $data['amount'];
                $wallet->total_deposited += $data['amount'];
                $wallet->save();

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'type' => 'deposit',
                    'amount' => $data['amount'],
                    'fee' => 0,
                    'net_amount' => $data['amount'],
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'status' => 'completed',
                    'reference' => $data['transaction_id'],
                    'description' => "Deposit via {$data['payment_method']}",
                    'metadata' => json_encode($data['metadata'] ?? []),
                    'completed_at' => now(),
                ]);

                if (isset($data['order_id'])) {
                    $this->processOrderPayment($user, $data['order_id']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment webhook processed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment webhook', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider la requête crypto
     */
    private function validateCryptoRequest($request)
    {
        return $request->validate([
            'transaction_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'network' => 'required|string',
            'status' => 'required|string|in:confirmed,pending,failed',
            'tx_hash' => 'nullable|string',
            'order_id' => 'nullable|string',
        ]);
    }

    /**
     * Valider la requête Mobile Money (Legacy)
     */
    private function validateMobileMoneyRequest($request)
    {
        return $request->validate([
            'transaction_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'provider' => 'required|string|in:Airtel,Orange,M-Pesa',
            'phone_number' => 'required|string',
            'status' => 'required|string|in:success,pending,failed',
            'order_id' => 'nullable|string',
        ]);
    }

    /**
     * Traiter le paiement d'une commande
     */
    private function processOrderPayment($user, $orderId)
    {
        $order = Order::where('order_number', $orderId)
            ->orWhere('id', $orderId)
            ->first();

        if (!$order) {
            Log::warning('Order not found', ['order_id' => $orderId]);
            return;
        }

        // Vérifier que la commande appartient à l'utilisateur
        if ($order->user_id !== $user->id) {
            Log::warning('Order does not belong to user', [
                'order_id' => $order->id,
                'order_user' => $order->user_id,
                'user' => $user->id
            ]);
            return;
        }

        // Mettre à jour la commande
        $order->payment_status = 'completed';
        $order->paid_at = now();
        $order->save();

        Log::info('Order paid successfully', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $user->id,
        ]);

        // Déclencher les commissions si nécessaire
        try {
            if (class_exists(\App\Services\MLM\MonthlyCommissionService::class)) {
                $this->commissionService->calculateOrderCommissions($order);
                Log::info('Commissions calculated for order', ['order_id' => $order->id]);
            }
        } catch (\Exception $e) {
            Log::error('Error calculating commissions', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        // Envoyer une notification
        try {
            $user->notify(new \App\Notifications\PaymentReceivedNotification(
                $order->total,
                'FlexPay',
                $order->id
            ));
        } catch (\Exception $e) {
            Log::error('Error sending payment notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Idempotence : true si l'événement peut être traité, false si déjà vu.
     */
    protected function markWebhookProcessed(string $provider, string $eventId): bool
    {
        $key = 'webhook:'.$provider.':'.hash('sha256', $eventId);

        return Cache::add($key, true, now()->addDays(7));
    }
}