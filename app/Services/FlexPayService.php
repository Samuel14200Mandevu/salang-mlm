<?php
// app/Services/FlexPayService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FlexPayService
{
    /**
     * Vérifier le statut d'un paiement FlexPay
     */
    public function verifierStatut($reference)
    {
        Log::info('FlexPayService: vérification statut', [
            'reference' => $reference
        ]);

        // Simulation : toujours retourner un succès
        return new class {
            public function isSuccessful()
            {
                return true;
            }
            
            public function getStatus()
            {
                return 'completed';
            }
            
            public function getData()
            {
                return [
                    'reference' => $this->reference ?? null,
                    'status' => 'completed',
                    'message' => 'Paiement vérifié'
                ];
            }
        };
    }

    /**
     * Initier un paiement FlexPay
     */
    public function initierPaiement($amount, $phone, $provider)
    {
        Log::info('FlexPayService: initiation paiement', [
            'amount' => $amount,
            'phone' => $phone,
            'provider' => $provider
        ]);

        return [
            'success' => true,
            'reference' => 'FLEX-' . strtoupper(uniqid()),
            'transaction_id' => 'FP-' . time() . '-' . rand(1000, 9999),
            'message' => 'Paiement initié avec succès'
        ];
    }

    /**
     * Confirmer un paiement FlexPay
     */
    public function confirmerPaiement($reference)
    {
        Log::info('FlexPayService: confirmation paiement', [
            'reference' => $reference
        ]);

        return [
            'success' => true,
            'status' => 'completed',
            'message' => 'Paiement confirmé'
        ];
    }

    /**
     * Annuler un paiement FlexPay
     */
    public function annulerPaiement($reference)
    {
        Log::info('FlexPayService: annulation paiement', [
            'reference' => $reference
        ]);

        return [
            'success' => true,
            'message' => 'Paiement annulé'
        ];
    }
}