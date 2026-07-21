<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use App\Services\MLM\MonthlyCommissionService;
use App\Services\PaymentService;
use App\Services\MobileMoneyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected MonthlyCommissionService $commissionService;
    protected PaymentService $paymentService;
    protected MobileMoneyService $mobileMoneyService;

    public function __construct(
        MonthlyCommissionService $commissionService,
        PaymentService $paymentService,
        MobileMoneyService $mobileMoneyService
    ) {
        $this->commissionService = $commissionService;
        $this->paymentService = $paymentService;
        $this->mobileMoneyService = $mobileMoneyService;
    }

    /**
     * Webhook pour FlexPay (Orange Money, Airtel Money, M-Pesa)
     */
    public function flexpay(Request $request)
    {
        Log::info('FlexPay Webhook received', $request->all());

        $data = $request->all();

        try {
            // Valider les données FlexPay
            if (!isset($data['orderNumber']) || !isset($data['status'])) {
                Log::error('FlexPay Webhook: Données invalides', $data);
                return response()->json(['error' => 'Données invalides'], 400);
            }

            $orderNumber = $data['orderNumber'];
            $status = $data['status'];
            $reference = $data['reference'] ?? null;
            $amount = $data['amount'] ?? 0;
            $phone = $data['phone'] ?? null;
            $provider = $data['provider'] ?? 'unknown';

            // Vérifier si la transaction existe déjà
            $existingTransaction = Transaction::where('reference', $reference)
                ->orWhere('transaction_id', $orderNumber)
                ->first();

            if ($existingTransaction) {
                // Mettre à jour le statut si nécessaire
                if ($status === 'SUCCESS' || $status === 'COMPLETED') {
                    if ($existingTransaction->status !== 'completed') {
                        return $this->confirmFlexPayTransaction($existingTransaction, $data);
                    }
                }
                return response()->json(['message' => 'Transaction déjà traitée'], 200);
            }

            // Si le paiement est réussi, créer la transaction
            if ($status === 'SUCCESS' || $status === 'COMPLETED') {
                return $this->processFlexPayPayment($data);
            }

            // Paiement échoué ou en attente
            Log::info('FlexPay Webhook: Statut non réussi', [
                'orderNumber' => $orderNumber,
                'status' => $status
            ]);

            return response()->json(['status' => 'success', 'message' => 'Webhook reçu'], 200);

        } catch (\Exception $e) {
            Log::error('FlexPay Webhook Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traiter un paiement FlexPay réussi
     */
    private function processFlexPayPayment(array $data)
    {
        DB::beginTransaction();

        try {
            $orderNumber = $data['orderNumber'];
            $reference = $data['reference'] ?? null;
            $amount = $data['amount'] ?? 0;
            $phone = $data['phone'] ?? null;
            $provider = $data['provider'] ?? 'unknown';
            $metadata = $data['metadata'] ?? [];

            // Récupérer l'utilisateur depuis les métadonnées
            $userId = $metadata['user_id'] ?? null;
            
            if (!$userId) {
                // Essayer de trouver via le wallet ou transaction existante
                Log::warning('FlexPay: User ID manquant dans les métadonnées', $data);
                return response()->json(['error' => 'User ID manquant'], 400);
            }

            $user = User::find($userId);
            if (!$user) {
                Log::error('FlexPay: Utilisateur non trouvé', ['user_id' => $userId]);
                return response()->json(['error' => 'Utilisateur non trouvé'], 404);
            }

            $wallet = $user->wallet;
            if (!$wallet) {
                Log::error('FlexPay: Portefeuille non trouvé', ['user_id' => $userId]);
                return response()->json(['error' => 'Portefeuille non trouvé'], 404);
            }

            // Vérifier si la transaction existe déjà
            $existing = Transaction::where('reference', $reference)
                ->orWhere('transaction_id', $orderNumber)
                ->first();

            if ($existing) {
                DB::rollBack();
                return response()->json(['message' => 'Transaction déjà traitée'], 200);
            }

            // Créer la transaction
            $balanceBefore = $wallet->balance;
            $wallet->balance += $amount;
            $wallet->total_deposited += $amount;
            $wallet->save();

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'reference' => $reference ?? $orderNumber,
                'transaction_id' => $orderNumber,
                'description' => "Dépôt via " . ucfirst($provider) . " Money (FlexPay)",
                'metadata' => json_encode([
                    'provider' => $provider,
                    'phone' => $phone,
                    'orderNumber' => $orderNumber,
                    'flexpay_data' => $data,
                ]),
                'completed_at' => now(),
            ]);

            Log::info('FlexPay: Transaction créée avec succès', [
                'transaction_id' => $transaction->id,
                'reference' => $reference,
                'user_id' => $user->id,
                'amount' => $amount
            ]);

            // Traiter la commande si elle existe
            if (isset($metadata['order_id'])) {
                $this->processOrderPayment($user, $metadata['order_id']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'FlexPay payment processed successfully',
                'transaction_id' => $transaction->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FlexPay: Erreur traitement paiement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Confirmer une transaction FlexPay existante
     */
    private function confirmFlexPayTransaction($transaction, array $data)
    {
        DB::beginTransaction();

        try {
            if ($transaction->status === 'completed') {
                DB::rollBack();
                return response()->json(['message' => 'Transaction déjà complétée'], 200);
            }

            $wallet = Wallet::find($transaction->wallet_id);
            if (!$wallet) {
                DB::rollBack();
                return response()->json(['error' => 'Portefeuille non trouvé'], 404);
            }

            $balanceBefore = $wallet->balance;
            $wallet->balance += $transaction->amount;
            $wallet->total_deposited += $transaction->amount;
            $wallet->save();

            $transaction->status = 'completed';
            $transaction->balance_before = $balanceBefore;
            $transaction->balance_after = $wallet->balance;
            $transaction->completed_at = now();
            $transaction->metadata = json_encode(array_merge(
                json_decode($transaction->metadata, true) ?? [],
                ['flexpay_confirm' => $data]
            ));
            $transaction->save();

            // Traiter la commande si elle existe
            $metadata = json_decode($transaction->metadata, true);
            if (isset($metadata['order_id'])) {
                $user = User::find($transaction->user_id);
                if ($user) {
                    $this->processOrderPayment($user, $metadata['order_id']);
                }
            }

            DB::commit();

            Log::info('FlexPay: Transaction confirmée', [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction confirmée'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FlexPay: Erreur confirmation transaction', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ]);
            throw $e;
        }
    }

    /**
     * Webhook Crypto (Coinbase)
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
}