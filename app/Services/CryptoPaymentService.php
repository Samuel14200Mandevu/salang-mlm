<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptoPaymentService
{
    protected $apiKey;
    protected $apiSecret;
    protected $webhookSecret;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('payment.crypto.api_key', '');
        $this->apiSecret = config('payment.crypto.api_secret', '');
        $this->webhookSecret = config('payment.crypto.webhook_secret', '');
        $this->baseUrl = config('payment.crypto.base_url', 'https://api.coinbase.com/v2');
    }

    /**
     * Créer un paiement crypto
     */
    public function createPayment($amount, $currency = 'USD', $cryptoCurrency = 'USDC', $userId = null, $orderId = null)
    {
        try {
            $response = Http::withHeaders([
                'X-CC-Api-Key' => $this->apiKey,
                'X-CC-Version' => '2018-03-22',
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/charges', [
                'name' => 'Achat de package Salang',
                'description' => 'Achat de package sur Salang MLM',
                'pricing_type' => 'fixed_price',
                'local_price' => [
                    'amount' => $amount,
                    'currency' => $currency,
                ],
                'metadata' => [
                    'user_id' => $userId,
                    'order_id' => $orderId,
                ],
                'redirect_url' => route('payment.success'),
                'cancel_url' => route('payment.cancel'),
                'webhook_url' => route('webhook.crypto'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'charge_id' => $data['data']['id'],
                    'payment_url' => $data['data']['hosted_url'],
                    'address' => $data['data']['addresses'][strtolower($cryptoCurrency)] ?? null,
                    'crypto_amount' => $data['data']['pricing'][$cryptoCurrency]['amount'] ?? null,
                ];
            }

            Log::error('Crypto payment creation failed', [
                'response' => $response->body(),
                'amount' => $amount,
            ]);

            return ['success' => false, 'error' => 'Erreur lors de la création du paiement'];

        } catch (\Exception $e) {
            Log::error('Crypto payment exception', [
                'error' => $e->getMessage(),
                'amount' => $amount,
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifier un paiement
     */
    public function checkPayment($chargeId)
    {
        try {
            $response = Http::withHeaders([
                'X-CC-Api-Key' => $this->apiKey,
                'X-CC-Version' => '2018-03-22',
            ])->get($this->baseUrl . '/charges/' . $chargeId);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['data']['status'] ?? 'pending';

                return [
                    'success' => true,
                    'status' => $status,
                    'data' => $data['data'],
                ];
            }

            return ['success' => false, 'error' => 'Paiement non trouvé'];

        } catch (\Exception $e) {
            Log::error('Check payment error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifier la signature du webhook
     */
    public function verifyWebhook($payload, $signature)
    {
        $computed = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($computed, $signature);
    }

    /**
     * Traiter un webhook crypto
     */
    public function processWebhook($data)
    {
        $event = $data['event'] ?? null;

        if (!$event) {
            return ['success' => false, 'error' => 'Événement invalide'];
        }

        $chargeId = $event['data']['id'] ?? null;
        $status = $event['data']['status'] ?? null;

        if (!$chargeId || !$status) {
            return ['success' => false, 'error' => 'Données invalides'];
        }

        // Trouver la transaction associée
        $transaction = Transaction::where('reference', $chargeId)->first();

        if (!$transaction) {
            $metadata = $event['data']['metadata'] ?? [];
            $user = User::find($metadata['user_id'] ?? null);

            if (!$user) {
                return ['success' => false, 'error' => 'Utilisateur non trouvé'];
            }

            $wallet = $user->wallet;
            if (!$wallet) {
                return ['success' => false, 'error' => 'Portefeuille non trouvé'];
            }

            $amount = $event['data']['pricing']['local']['amount'] ?? 0;

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance + $amount,
                'status' => 'pending',
                'reference' => $chargeId,
                'description' => 'Dépôt crypto en attente',
                'metadata' => json_encode($event['data']),
            ]);
        }

        if ($status === 'confirmed' || $status === 'completed') {
            return $this->confirmPayment($transaction);
        } elseif ($status === 'failed' || $status === 'cancelled') {
            $transaction->status = 'failed';
            $transaction->save();
            return ['success' => true, 'status' => $status];
        }

        return ['success' => true, 'status' => $status];
    }

    /**
     * Confirmer un paiement
     */
    public function confirmPayment($transaction)
    {
        $wallet = Wallet::find($transaction->wallet_id);
        $user = User::find($transaction->user_id);

        if (!$wallet || !$user) {
            return ['success' => false, 'error' => 'Portefeuille ou utilisateur non trouvé'];
        }

        $balanceBefore = $wallet->balance;
        $wallet->balance += $transaction->amount;
        $wallet->total_deposited += $transaction->amount;
        $wallet->save();

        $transaction->status = 'completed';
        $transaction->balance_before = $balanceBefore;
        $transaction->balance_after = $wallet->balance;
        $transaction->completed_at = now();
        $transaction->save();

        $metadata = json_decode($transaction->metadata, true);
        $orderId = $metadata['order_id'] ?? null;

        if ($orderId) {
            $order = Order::where('order_number', $orderId)->first();
            if ($order) {
                $order->payment_status = 'completed';
                $order->paid_at = now();
                $order->save();

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

        try {
            $user->notify(new \App\Notifications\PaymentReceivedNotification(
                $transaction->amount,
                'Crypto',
                $transaction->id
            ));
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification paiement', ['error' => $e->getMessage()]);
        }

        return ['success' => true];
    }

    /**
     * Obtenir les taux de change crypto
     */
    public function getExchangeRates()
    {
        try {
            $response = Http::get($this->baseUrl . '/exchange-rates');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'rates' => $response->json()['data']['rates'] ?? [],
                ];
            }

            return ['success' => false, 'error' => 'Impossible de récupérer les taux'];

        } catch (\Exception $e) {
            Log::error('Exchange rates error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}