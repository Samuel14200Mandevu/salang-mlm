<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MobileMoneyService
{
    protected $apiKey;
    protected $apiSecret;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('payment.mobile_money.api_key', '');
        $this->apiSecret = config('payment.mobile_money.api_secret', '');
        $this->baseUrl = config('payment.mobile_money.base_url', '');
    }

    /**
     * Initier un paiement Mobile Money
     */
    public function initiatePayment($amount, $phoneNumber, $provider, $userId = null, $orderId = null)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json',
                'X-API-Key' => $this->apiKey,
            ])->post($this->baseUrl . '/payment/initiate', [
                'amount' => $amount,
                'currency' => 'XOF',
                'phone' => $phoneNumber,
                'provider' => $provider,
                'reference' => 'SALANG-' . date('Ymd') . '-' . uniqid(),
                'metadata' => [
                    'user_id' => $userId,
                    'order_id' => $orderId,
                ],
                'webhook_url' => route('webhook.mobile-money'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'reference' => $data['reference'] ?? null,
                    'status' => $data['status'] ?? 'pending',
                ];
            }

            Log::error('Mobile Money payment initiation failed', [
                'response' => $response->body(),
                'phone' => $phoneNumber,
                'amount' => $amount,
            ]);

            return ['success' => false, 'error' => 'Erreur lors de l\'initiation du paiement'];

        } catch (\Exception $e) {
            Log::error('Mobile Money exception', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber,
                'amount' => $amount,
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifier un paiement Mobile Money
     */
    public function checkPayment($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'X-API-Key' => $this->apiKey,
            ])->get($this->baseUrl . '/payment/status/' . $reference);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'pending',
                    'data' => $data,
                ];
            }

            return ['success' => false, 'error' => 'Paiement non trouvé'];

        } catch (\Exception $e) {
            Log::error('Check mobile money payment error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Confirmer un paiement Mobile Money
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
                'Mobile Money',
                $transaction->id
            ));
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification paiement', ['error' => $e->getMessage()]);
        }

        return ['success' => true];
    }

    /**
     * Obtenir le token d'accès
     */
    private function getAccessToken()
    {
        try {
            $response = Http::post($this->baseUrl . '/auth/token', [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'] ?? null;
            }

            Log::error('Mobile Money token error', ['response' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Mobile Money token exception', ['error' => $e->getMessage()]);
            return null;
        }
    }
}