<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Order;
use Devscast\Flexpay\Flexpay;
use Devscast\Flexpay\Credentials;
use Devscast\Flexpay\Environment;
use Devscast\Flexpay\Data\Currency;
use Devscast\Flexpay\Requests\MobileRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MobileMoneyService
{
    protected Flexpay $flexpay;
    protected array $providers = ['orange', 'airtel', 'mpesa'];

    public function __construct()
    {
        $this->flexpay = new Flexpay(
            new Credentials(
                token: config('services.flexpay.token'),
                merchantCode: config('services.flexpay.merchant_code')
            ),
            environment: config('services.flexpay.environment') === 'live' 
                ? Environment::LIVE 
                : Environment::SANDBOX
        );
    }

    /**
     * Initier un paiement Mobile Money
     */
    public function initiatePayment($amount, $phoneNumber, $provider, $userId = null, $orderId = null)
    {
        $provider = strtolower($provider);

        if (!in_array($provider, $this->providers)) {
            return [
                'success' => false,
                'error' => 'Provider non supporté. Utilisez Orange, Airtel ou M-Pesa.'
            ];
        }

        // Nettoyer le numéro de téléphone
        $phoneNumber = $this->cleanPhoneNumber($phoneNumber);

        try {
            // Générer une référence unique
            $reference = 'SALANG-' . strtoupper($provider) . '-' . date('Ymd') . '-' . uniqid();

            // Description du paiement
            $description = 'Achat Salang MLM - ' . ucfirst($provider) . ' Money';

            // Créer la requête mobile FlexPay
            $mobile = new MobileRequest(
                amount: (float) $amount,
                currency: Currency::USD, // ou Currency::CDF pour Francs Congolais
                phone: $phoneNumber,
                reference: $reference,
                description: $description,
                callbackUrl: route('webhook.flexpay'),
            );

            // Envoyer la requête à FlexPay
            $response = $this->flexpay->pay($mobile);

            Log::info('FlexPay Mobile Money payment initiated', [
                'provider' => $provider,
                'reference' => $reference,
                'response' => $response
            ]);

            return [
                'success' => true,
                'transaction_id' => $response['orderNumber'] ?? null,
                'reference' => $reference,
                'provider' => $provider,
                'status' => 'pending',
                'message' => 'Paiement ' . ucfirst($provider) . ' Money initié. Veuillez confirmer sur votre téléphone.',
                'raw_response' => $response
            ];

        } catch (\Exception $e) {
            Log::error('FlexPay Mobile Money exception', [
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
     * Vérifier un paiement Mobile Money
     */
    public function checkPayment($reference, $provider = null)
    {
        try {
            $state = $this->flexpay->check($reference);

            if ($state->isSuccessful()) {
                return [
                    'success' => true,
                    'status' => 'completed',
                    'data' => $state,
                ];
            }

            // Vérifier si la transaction est en attente
            $pendingStatuses = ['pending', 'initiated', 'processing'];
            if (in_array($state->getStatus(), $pendingStatuses)) {
                return [
                    'success' => true,
                    'status' => 'pending',
                    'data' => $state,
                ];
            }

            return [
                'success' => false,
                'status' => 'failed',
                'data' => $state,
            ];

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
     * Nettoyer le numéro de téléphone
     */
    private function cleanPhoneNumber(string $phone): string
    {
        // Enlever tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Si le numéro commence par 0, remplacer par 243 (RDC)
        if (strpos($phone, '0') === 0) {
            $phone = '243' . substr($phone, 1);
        }
        
        // Si le numéro ne commence pas par 243, ajouter 243
        if (strpos($phone, '243') !== 0 && strlen($phone) < 10) {
            $phone = '243' . $phone;
        }

        return $phone;
    }

    /**
     * Obtenir le préfixe de l'opérateur
     */
    public function getOperatorPrefix($phone): ?string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Extraire les 2 premiers chiffres après 243
        if (strpos($phone, '243') === 0 && strlen($phone) >= 5) {
            $prefix = substr($phone, 3, 2);
            return $prefix;
        }
        
        return null;
    }

    /**
     * Valider le numéro pour un opérateur spécifique
     */
    public function validateNumberForOperator($phone, $provider): bool
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $prefix = $this->getOperatorPrefix($phone);

        if (!$prefix) {
            return false;
        }

        switch ($provider) {
            case 'orange':
                // Orange RDC : 8X ou 9X
                return in_array($prefix, ['80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99']);
            case 'airtel':
                // Airtel RDC : 7X
                return in_array($prefix, ['70', '71', '72', '73', '74', '75', '76', '77', '78', '79']);
            case 'mpesa':
                // M-Pesa (Vodacom) RDC : 8X ou 99X
                return in_array($prefix, ['80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '99']);
            default:
                return false;
        }
    }
}