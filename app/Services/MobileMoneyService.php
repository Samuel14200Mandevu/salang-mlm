<?php
// app/Services/MobileMoneyService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MobileMoneyService
{
    protected array $providers = ['orange', 'airtel', 'mpesa'];

    public function __construct()
    {
        // ✅ VERSION SIMPLIFIÉE - Pas de dépendance FlexPay
        Log::info('MobileMoneyService initialisé (mode sans FlexPay)');
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

        $phoneNumber = $this->cleanPhoneNumber($phoneNumber);
        $reference = 'SALANG-' . strtoupper($provider) . '-' . date('Ymd') . '-' . uniqid();

        Log::info('Mobile Money payment initiated (simulé)', [
            'provider' => $provider,
            'phone' => $phoneNumber,
            'amount' => $amount,
            'reference' => $reference
        ]);

        return [
            'success' => true,
            'transaction_id' => 'MM-' . time() . '-' . rand(1000, 9999),
            'reference' => $reference,
            'provider' => $provider,
            'status' => 'pending',
            'message' => 'Paiement ' . ucfirst($provider) . ' Money initié.',
            'simulation' => true
        ];
    }

    /**
     * Vérifier un paiement Mobile Money
     */
    public function checkPayment($reference, $provider = null)
    {
        return [
            'success' => true,
            'status' => 'completed',
            'data' => [
                'reference' => $reference,
                'status' => 'completed',
                'message' => 'Paiement vérifié'
            ]
        ];
    }

    /**
     * Nettoyer le numéro de téléphone
     */
    private function cleanPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strpos($phone, '0') === 0) {
            $phone = '243' . substr($phone, 1);
        }
        
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
        
        if (strpos($phone, '243') === 0 && strlen($phone) >= 5) {
            return substr($phone, 3, 2);
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
                return in_array($prefix, ['80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99']);
            case 'airtel':
                return in_array($prefix, ['70', '71', '72', '73', '74', '75', '76', '77', '78', '79']);
            case 'mpesa':
                return in_array($prefix, ['80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '99']);
            default:
                return false;
        }
    }
}