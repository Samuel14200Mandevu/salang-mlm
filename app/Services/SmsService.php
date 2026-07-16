<?php
// app/Services/SmsService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SmsService
{
    /**
     * Providers disponibles
     */
    protected array $providers = ['orange', 'airtel', 'vodacom'];

    /**
     * Envoyer un code d'activation par SMS
     */
    public function sendActivationCode(string $phone, string $code, string $provider = null): bool
    {
        $phone = $this->cleanPhoneNumber($phone);

        if (!$provider) {
            $provider = $this->detectProvider($phone);
        }

        $message = "Votre code d'activation Salang MLM : {$code}. Valable 7 jours.";

        switch ($provider) {
            case 'orange':
                return $this->sendViaOrange($phone, $message);
            case 'airtel':
                return $this->sendViaAirtel($phone, $message);
            case 'vodacom':
                return $this->sendViaVodacom($phone, $message);
            default:
                Log::warning('Provider non supporte', ['provider' => $provider]);
                return false;
        }
    }

    /**
     * Détecter le provider en fonction du préfixe
     */
    public function detectProvider(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        $prefixes = [
            // Côte d'Ivoire (+225)
            '225' => [
                'orange' => ['07', '08', '09', '05'],
                'airtel' => ['01', '02', '03', '04'],
                'vodacom' => ['06', '15'],
            ],
            // Sénégal (+221)
            '221' => [
                'orange' => ['70', '76', '77'],
                'airtel' => ['78', '79'],
                'vodacom' => ['75'],
            ],
            // Cameroun (+237)
            '237' => [
                'orange' => ['65', '66', '67'],
                'airtel' => ['68', '69'],
                'vodacom' => ['61', '62'],
            ],
            // RDC (+243)
            '243' => [
                'orange' => ['81', '82', '83', '84', '85'],
                'airtel' => ['80', '86', '87', '88', '89'],
                'vodacom' => ['97', '98', '99'],
            ],
            // Guinée (+224)
            '224' => [
                'orange' => ['60', '61', '62', '63'],
                'airtel' => ['65', '66', '67'],
                'vodacom' => ['68', '69'],
            ],
            // Burkina Faso (+226)
            '226' => [
                'orange' => ['60', '61', '62', '63', '64'],
                'airtel' => ['70', '71', '72', '73'],
                'vodacom' => ['55', '56', '57'],
            ],
        ];

        foreach ($prefixes as $countryCode => $providers) {
            if (str_starts_with($phone, $countryCode)) {
                $rest = substr($phone, strlen($countryCode));
                foreach ($providers as $provider => $prefixList) {
                    foreach ($prefixList as $prefix) {
                        if (str_starts_with($rest, $prefix)) {
                            return $provider;
                        }
                    }
                }
            }
        }

        return 'orange';
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

    /**
     * Envoyer via Orange
     */
    private function sendViaOrange(string $phone, string $message): bool
    {
        try {
            $apiKey = env('ORANGE_API_KEY');
            $apiSecret = env('ORANGE_API_SECRET');
            $baseUrl = env('ORANGE_BASE_URL', 'https://api.orange.com');
            $sender = env('ORANGE_SENDER', 'Salang');

            $token = $this->getOrangeToken($apiKey, $apiSecret, $baseUrl);
            if (!$token) {
                Log::error('Impossible d\'obtenir le token Orange');
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ])->post("{$baseUrl}/sms/v1/outbound", [
                'sender' => $sender,
                'recipient' => $this->formatPhoneForProvider($phone),
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info('SMS envoye avec Orange', ['phone' => $phone]);
                return true;
            }

            Log::error('Erreur Orange SMS', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Erreur Orange: ' . $e->getMessage());
            return false;
        }
    }

    private function getOrangeToken(string $apiKey, string $apiSecret, string $baseUrl): ?string
    {
        $cacheKey = 'orange_token';
        
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

                Log::error('Erreur token Orange', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                ]);
                return null;

            } catch (\Exception $e) {
                Log::error('Erreur token Orange: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Envoyer via Airtel
     */
    private function sendViaAirtel(string $phone, string $message): bool
    {
        try {
            $apiKey = env('AIRTEL_API_KEY');
            $apiSecret = env('AIRTEL_API_SECRET');
            $baseUrl = env('AIRTEL_BASE_URL', 'https://api.airtel.africa');
            $sender = env('AIRTEL_SENDER', 'Salang');

            $token = $this->getAirtelToken($apiKey, $apiSecret, $baseUrl);
            if (!$token) {
                Log::error('Impossible d\'obtenir le token Airtel');
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$baseUrl}/sms/v1/send", [
                'sender' => $sender,
                'to' => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info('SMS envoye avec Airtel', ['phone' => $phone]);
                return true;
            }

            Log::error('Erreur Airtel SMS', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Erreur Airtel: ' . $e->getMessage());
            return false;
        }
    }

    private function getAirtelToken(string $apiKey, string $apiSecret, string $baseUrl): ?string
    {
        $cacheKey = 'airtel_token';
        
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

                Log::error('Erreur token Airtel', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                ]);
                return null;

            } catch (\Exception $e) {
                Log::error('Erreur token Airtel: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Envoyer via Vodacom
     */
    private function sendViaVodacom(string $phone, string $message): bool
    {
        try {
            $apiKey = env('VODACOM_API_KEY');
            $apiSecret = env('VODACOM_API_SECRET');
            $baseUrl = env('VODACOM_BASE_URL', 'https://api.vodacom.com');
            $sender = env('VODACOM_SENDER', 'Salang');

            $token = $this->getVodacomToken($apiKey, $apiSecret, $baseUrl);
            if (!$token) {
                Log::error('Impossible d\'obtenir le token Vodacom');
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ])->post("{$baseUrl}/messaging/v1/sms", [
                'from' => $sender,
                'to' => $phone,
                'text' => $message,
            ]);

            if ($response->successful()) {
                Log::info('SMS envoye avec Vodacom', ['phone' => $phone]);
                return true;
            }

            Log::error('Erreur Vodacom SMS', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Erreur Vodacom: ' . $e->getMessage());
            return false;
        }
    }

    private function getVodacomToken(string $apiKey, string $apiSecret, string $baseUrl): ?string
    {
        $cacheKey = 'vodacom_token';
        
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

                Log::error('Erreur token Vodacom', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                ]);
                return null;

            } catch (\Exception $e) {
                Log::error('Erreur token Vodacom: ' . $e->getMessage());
                return null;
            }
        });
    }

    private function formatPhoneForProvider(string $phone): string
    {
        return ltrim($phone, '+');
    }
}