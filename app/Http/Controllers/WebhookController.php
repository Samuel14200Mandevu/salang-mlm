<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Webhook pour les paiements crypto
     */
    public function crypto(Request $request)
    {
        Log::info('Webhook Crypto reçu', $request->all());

        // Validation de la signature (à adapter selon votre fournisseur)
        // if (!$this->verifySignature($request)) {
        //     return response()->json(['error' => 'Invalid signature'], 401);
        // }

        $data = $request->validate([
            'transaction_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'network' => 'required|string',
            'status' => 'required|string|in:confirmed,pending,failed',
            'tx_hash' => 'nullable|string',
            'order_id' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = User::find($data['user_id']);
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            // Vérifier si la transaction existe déjà
            $existing = Transaction::where('reference', $data['transaction_id'])->first();
            if ($existing) {
                return response()->json(['message' => 'Transaction already processed'], 200);
            }

            if ($data['status'] === 'confirmed') {
                // Créditer le portefeuille
                $balanceBefore = $wallet->balance;
                $wallet->balance += $data['amount'];
                $wallet->total_deposited += $data['amount'];
                $wallet->save();

                // Créer la transaction
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
                    'description' => "Dépôt crypto {$data['currency']} sur {$data['network']}",
                    'metadata' => json_encode([
                        'tx_hash' => $data['tx_hash'] ?? null,
                        'network' => $data['network'],
                        'currency' => $data['currency'],
                    ]),
                    'completed_at' => now(),
                ]);

                // Si c'est pour une commande
                if (isset($data['order_id'])) {
                    $order = Order::where('order_number', $data['order_id'])->first();
                    if ($order) {
                        $order->payment_status = 'completed';
                        $order->paid_at = now();
                        $order->save();

                        // Traiter les commissions si c'est un package
                        if ($order->items()->whereNotNull('package_id')->exists()) {
                            $commissionService = new CommissionService();
                            foreach ($order->items as $item) {
                                if ($item->package_id) {
                                    $commissionService->calculatePackageCommission(
                                        $user->id,
                                        $item->package_id,
                                        $order->id
                                    );
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Webhook crypto traité avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur webhook crypto', [
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
     * Webhook pour les paiements Mobile Money
     */
    public function mobileMoney(Request $request)
    {
        Log::info('Webhook Mobile Money reçu', $request->all());

        // Validation de la signature (à adapter selon votre fournisseur)
        // if (!$this->verifyMobileMoneySignature($request)) {
        //     return response()->json(['error' => 'Invalid signature'], 401);
        // }

        $data = $request->validate([
            'transaction_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'provider' => 'required|string|in:Airtel,Orange,M-Pesa',
            'phone_number' => 'required|string',
            'status' => 'required|string|in:success,pending,failed',
            'order_id' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = User::find($data['user_id']);
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            // Vérifier si la transaction existe déjà
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
                    'description' => "Dépôt Mobile Money {$data['provider']}",
                    'metadata' => json_encode([
                        'provider' => $data['provider'],
                        'phone_number' => $data['phone_number'],
                    ]),
                    'completed_at' => now(),
                ]);

                // Si c'est pour une commande
                if (isset($data['order_id'])) {
                    $order = Order::where('order_number', $data['order_id'])->first();
                    if ($order) {
                        $order->payment_status = 'completed';
                        $order->paid_at = now();
                        $order->save();

                        // Traiter les commissions si c'est un package
                        if ($order->items()->whereNotNull('package_id')->exists()) {
                            $commissionService = new CommissionService();
                            foreach ($order->items as $item) {
                                if ($item->package_id) {
                                    $commissionService->calculatePackageCommission(
                                        $user->id,
                                        $item->package_id,
                                        $order->id
                                    );
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Webhook Mobile Money traité avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur webhook mobile money', [
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
     * Webhook générique pour les paiements
     */
    public function payment(Request $request)
    {
        Log::info('Webhook Payment reçu', $request->all());

        $data = $request->validate([
            'transaction_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'status' => 'required|string|in:completed,pending,failed',
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
                    'description' => "Dépôt via {$data['payment_method']}",
                    'metadata' => json_encode($data['metadata'] ?? []),
                    'completed_at' => now(),
                ]);

                if (isset($data['order_id'])) {
                    $order = Order::where('order_number', $data['order_id'])->first();
                    if ($order) {
                        $order->payment_status = 'completed';
                        $order->paid_at = now();
                        $order->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Webhook payment traité avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur webhook payment', [
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
     * Vérifier la signature du webhook crypto
     */
    private function verifySignature($request)
    {
        // À implémenter selon votre fournisseur crypto
        // Exemple: Coinbase, Stripe, etc.
        $signature = $request->header('X-Signature');
        $payload = $request->getContent();
        $secret = config('services.crypto.webhook_secret');

        // $computed = hash_hmac('sha256', $payload, $secret);
        // return hash_equals($computed, $signature);

        return true; // À remplacer par une vraie vérification
    }

    /**
     * Vérifier la signature du webhook Mobile Money
     */
    private function verifyMobileMoneySignature($request)
    {
        // À implémenter selon votre fournisseur Mobile Money
        // Exemple: Airtel, Orange, etc.
        return true; // À remplacer par une vraie vérification
    }
}