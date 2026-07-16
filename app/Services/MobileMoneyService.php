<?php
// app/Services/MobileMoneyService.php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MobileMoneyService
{
    protected array $providers = ['orange', 'airtel', 'vodacom'];

    /**
     * Initier un paiement Mobile Money
     */
    public function initiatePayment($amount, $phoneNumber, $provider, $userId = null, $orderId = null)
    {
        $provider = strtolower($provider);

        if (!in_array($provider, $this->providers)) {
            return [
                'success' => false,
                'error' => 'Provider non supporté. Utilisez Orange, Airtel ou Vodacom.'
            ];
        }

        // Nettoyer le numéro de téléphone
        $phoneNumber = $this->cleanPhoneNumber($phoneNumber);

        try {
            $result = $this->{'initiate' . ucfirst($provider) . 'Payment'}(
                $amount,
                $phoneNumber,
                $userId,
                $orderId
            );

            return $result;

        } catch (\Exception $e) {
            Log::error('Mobile Money exception', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'phone' => $phoneNumber,
                'amount' => $amount,
            ]);
            return [
                'success' => false,
                'error' => 'Erreur technique: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Initier paiement Orange Money
     */
    private function initiateOrangePayment($amount, $phoneNumber, $userId, $orderId): array
    {
        try {
            $apiKey = env('ORANGE_MONEY_API_KEY');
            $apiSecret = env('ORANGE_MONEY_API_SECRET');
            $baseUrl = env('ORANGE_MONEY_BASE_URL', 'https://api.orange.com');

            $token = $this->getOrangeToken($apiKey, $apiSecret, $baseUrl);

            if (!$token) {
                return [
                    'success' => false,
                    'error' => 'Erreur d\'authentification Orange Money'
                ];
            }

            $reference = 'SALANG-OR-' . date('Ymd') . '-' . uniqid();

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$baseUrl}/payments/v1/transaction", [
                'amount' => $amount,
                'currency' => 'XOF',
                'phone' => $phoneNumber,
                'reference' => $reference,
                'description' => 'Achat Salang MLM',
                'callback_url' => route('webhook.orange-money'),
                'metadata' => [
                    'user_id' => $userId,
                    'order_id' => $orderId,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'reference' => $reference,
                    'provider' => 'orange',
                    'status' => 'pending',
                    'message' => 'Paiement Orange Money initié',
                ];
            }

            Log::error('Orange Money payment failed', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors du paiement Orange Money'
            ];

        } catch (\Exception $e) {
            Log::error('Orange Money exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Initier paiement Airtel Money
     */
    private function initiateAirtelPayment($amount, $phoneNumber, $userId, $orderId): array
    {
        try {
            $apiKey = env('AIRTEL_MONEY_API_KEY');
            $apiSecret = env('AIRTEL_MONEY_API_SECRET');
            $baseUrl = env('AIRTEL_MONEY_BASE_URL', 'https://api.airtel.africa');

            $token = $this->getAirtelToken($apiKey, $apiSecret, $baseUrl);

            if (!$token) {
                return [
                    'success' => false,
                    'error' => 'Erreur d\'authentification Airtel Money'
                ];
            }

            $reference = 'SALANG-AR-' . date('Ymd') . '-' . uniqid();

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$baseUrl}/payments/v1/collect", [
                'amount' => $amount,
                'currency' => 'XOF',
                'phone' => $phoneNumber,
                'reference' => $reference,
                'description' => 'Achat Salang MLM',
                'callback_url' => route('webhook.airtel-money'),
                'metadata' => [
                    'user_id' => $userId,
                    'order_id' => $orderId,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'reference' => $reference,
                    'provider' => 'airtel',
                    'status' => 'pending',
                    'message' => 'Paiement Airtel Money initié',
                ];
            }

            Log::error('Airtel Money payment failed', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors du paiement Airtel Money'
            ];

        } catch (\Exception $e) {
            Log::error('Airtel Money exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Initier paiement Vodacom M-Pesa
     */
    private function initiateVodacomPayment($amount, $phoneNumber, $userId, $orderId): array
    {
        try {
            $apiKey = env('VODACOM_MONEY_API_KEY');
            $apiSecret = env('VODACOM_MONEY_API_SECRET');
            $baseUrl = env('VODACOM_MONEY_BASE_URL', 'https://api.vodacom.com');

            $token = $this->getVodacomToken($apiKey, $apiSecret, $baseUrl);

            if (!$token) {
                return [
                    'success' => false,
                    'error' => 'Erreur d\'authentification Vodacom M-Pesa'
                ];
            }

            $reference = 'SALANG-VC-' . date('Ymd') . '-' . uniqid();

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$baseUrl}/mpesa/v1/payment", [
                'amount' => $amount,
                'currency' => 'CDF',
                'phone' => $phoneNumber,
                'reference' => $reference,
                'description' => 'Achat Salang MLM',
                'callback_url' => route('webhook.vodacom-money'),
                'metadata' => [
                    'user_id' => $userId,
                    'order_id' => $orderId,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'reference' => $reference,
                    'provider' => 'vodacom',
                    'status' => 'pending',
                    'message' => 'Paiement Vodacom M-Pesa initié',
                ];
            }

            Log::error('Vodacom M-Pesa payment failed', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors du paiement Vodacom M-Pesa'
            ];

        } catch (\Exception $e) {
            Log::error('Vodacom M-Pesa exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier un paiement Mobile Money
     */
    public function checkPayment($reference, $provider = null)
    {
        $provider = strtolower($provider ?? 'orange');

        if (!in_array($provider, $this->providers)) {
            return ['success' => false, 'error' => 'Provider non supporté'];
        }

        try {
            $result = $this->{'check' . ucfirst($provider) . 'Payment'}($reference);
            return $result;

        } catch (\Exception $e) {
            Log::error('Check payment error', [
                'provider' => $provider,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifier paiement Orange
     */
    private function checkOrangePayment($reference): array
    {
        try {
            $apiKey = env('ORANGE_MONEY_API_KEY');
            $apiSecret = env('ORANGE_MONEY_API_SECRET');
            $baseUrl = env('ORANGE_MONEY_BASE_URL');

            $token = $this->getOrangeToken($apiKey, $apiSecret, $baseUrl);

            if (!$token) {
                return ['success' => false, 'error' => 'Erreur d\'authentification'];
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
            ])->get("{$baseUrl}/payments/v1/transaction/{$reference}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'pending',
                    'data' => $data,
                ];
            }

            return ['success' => false, 'error' => 'Transaction non trouvée'];

        } catch (\Exception $e) {
            Log::error('Check Orange payment error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifier paiement Airtel
     */
    private function checkAirtelPayment($reference): array
    {
        try {
            $apiKey = env('AIRTEL_MONEY_API_KEY');
            $apiSecret = env('AIRTEL_MONEY_API_SECRET');
            $baseUrl = env('AIRTEL_MONEY_BASE_URL');

            $token = $this->getAirtelToken($apiKey, $apiSecret, $baseUrl);

            if (!$token) {
                return ['success' => false, 'error' => 'Erreur d\'authentification'];
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
            ])->get("{$baseUrl}/payments/v1/transaction/{$reference}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'pending',
                    'data' => $data,
                ];
            }

            return ['success' => false, 'error' => 'Transaction non trouvée'];

        } catch (\Exception $e) {
            Log::error('Check Airtel payment error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifier paiement Vodacom
     */
    private function checkVodacomPayment($reference): array
    {
        try {
            $apiKey = env('VODACOM_MONEY_API_KEY');
            $apiSecret = env('VODACOM_MONEY_API_SECRET');
            $baseUrl = env('VODACOM_MONEY_BASE_URL');

            $token = $this->getVodacomToken($apiKey, $apiSecret, $baseUrl);

            if (!$token) {
                return ['success' => false, 'error' => 'Erreur d\'authentification'];
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
            ])->get("{$baseUrl}/mpesa/v1/payment/{$reference}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'pending',
                    'data' => $data,
                ];
            }

            return ['success' => false, 'error' => 'Transaction non trouvée'];

        } catch (\Exception $e) {
            Log::error('Check Vodacom payment error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtenir token Orange
     */
    private function getOrangeToken($apiKey, $apiSecret, $baseUrl): ?string
    {
        $cacheKey = 'orange_payment_token';
        
        return Cache::remember($cacheKey, 3500, function () use ($apiKey, $apiSecret, $baseUrl) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode("{$apiKey}:{$apiSecret}"),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])->post("{$baseUrl}/oauth/v2/token", [
                    'grant_type' => 'client_credentials',
                ]);

                if ($response->successful()) {
                    return $response->json('access_token');
                }

                Log::error('Orange token error', ['response' => $response->body()]);
                return null;

            } catch (\Exception $e) {
                Log::error('Orange token exception: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Obtenir token Airtel
     */
    private function getAirtelToken($apiKey, $apiSecret, $baseUrl): ?string
    {
        $cacheKey = 'airtel_payment_token';
        
        return Cache::remember($cacheKey, 3500, function () use ($apiKey, $apiSecret, $baseUrl) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("{$baseUrl}/oauth/v1/token", [
                    'grant_type' => 'client_credentials',
                    'client_id' => $apiKey,
                    'client_secret' => $apiSecret,
                ]);

                if ($response->successful()) {
                    return $response->json('access_token');
                }

                Log::error('Airtel token error', ['response' => $response->body()]);
                return null;

            } catch (\Exception $e) {
                Log::error('Airtel token exception: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Obtenir token Vodacom
     */
    private function getVodacomToken($apiKey, $apiSecret, $baseUrl): ?string
    {
        $cacheKey = 'vodacom_payment_token';
        
        return Cache::remember($cacheKey, 3500, function () use ($apiKey, $apiSecret, $baseUrl) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("{$baseUrl}/oauth/v1/token", [
                    'grant_type' => 'client_credentials',
                    'client_id' => $apiKey,
                    'client_secret' => $apiSecret,
                ]);

                if ($response->successful()) {
                    return $response->json('access_token');
                }

                Log::error('Vodacom token error', ['response' => $response->body()]);
                return null;

            } catch (\Exception $e) {
                Log::error('Vodacom token exception: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Nettoyer le numéro de téléphone
     */
    private function cleanPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '225' . substr($phone, 1);
        }
        
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }
}