<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Taux de commission
    |--------------------------------------------------------------------------
    */
    'rates' => [
        'direct' => 30,        // 30% pour le parrain direct
        'indirect' => 15,      // 15% pour le parrain du parrain
        'leadership' => 10,    // 10% pour les leaders
        'retail' => 25,        // 25% pour la vente au détail
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Seuils de leadership
    |--------------------------------------------------------------------------
    */
    'leadership' => [
        'min_pv' => 1000,
        'max_levels' => 5,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Frais de retrait
    |--------------------------------------------------------------------------
    */
    'withdrawal_fee' => 2.5, // 2.5%
    
    /*
    |--------------------------------------------------------------------------
    | Montant minimum de retrait
    |--------------------------------------------------------------------------
    */
    'min_withdrawal' => 10,
    
    /*
    |--------------------------------------------------------------------------
    | Niveaux de commission (Unilevel)
    |--------------------------------------------------------------------------
    */
    'levels' => [
        1 => 30,   // Niveau 1 (direct) : 30%
        2 => 15,   // Niveau 2 : 15%
        3 => 10,   // Niveau 3 : 10%
        4 => 5,    // Niveau 4 : 5%
        5 => 5,    // Niveau 5 : 5%
    ],
    /*
    |--------------------------------------------------------------------------
    | Constantes pour les calculs
    |--------------------------------------------------------------------------
    */
    'constants' => [
        'MAX_UNILEVEL_LEVELS' => 10,
        'DIRECT_COMMISSION_LABEL' => 'Direct Bonus',
        'INDIRECT_COMMISSION_LABEL' => 'Indirect Bonus',
        'LEADERSHIP_COMMISSION_LABEL' => 'Leadership Bonus',
        'RETAIL_PROFIT_LABEL' => 'Retail Profit',
    ],
     /*
    |--------------------------------------------------------------------------
    | Limites de sécurité
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'max_commission_per_transaction' => 10000,
        'max_commission_per_day' => 50000,
        'max_withdrawal_per_day' => 5000,
        'min_withdrawal_amount' => 10,
        'max_withdrawal_amount' => 10000,
    ],
];