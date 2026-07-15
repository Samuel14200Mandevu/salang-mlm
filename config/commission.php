<?php
// config/commission.php

return [
    /*
    |--------------------------------------------------------------------------
    | Commission Rates - Salang MLM Plan
    |--------------------------------------------------------------------------
    */

    'rates' => [
        'levels' => [
            1 => 0,
            2 => 6,
            3 => 22,
            4 => 26,
            5 => 30,
            6 => 34,
            7 => 40,
            8 => 43,
            9 => 45,
        ],

        'leadership' => [
            5 => 0.5,
            6 => 1.1,
            7 => 1.8,
            8 => 2.6,
            9 => 3.5,
        ],

        'leadership_conditions' => [
            5 => ['personal_pv' => 30, 'group_pv' => 500],
            6 => ['personal_pv' => 50, 'group_pv' => 1000],
            7 => ['personal_pv' => 100, 'group_pv' => 2000],
            8 => ['personal_pv' => 180, 'group_pv' => 3000],
            9 => ['personal_pv' => 300, 'group_pv' => 5000],
        ],

        'retail' => 25,
        'consumer_bonus' => 6,
        'global_bonus_pool' => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rank Thresholds (Minimum PV)
    |--------------------------------------------------------------------------
    */

    'rank_thresholds' => [
        1 => 0,
        2 => 100,
        3 => 200,
        4 => 1000,
        5 => 3800,
        6 => 16000,
        7 => 73000,
        8 => 280000,
        9 => 400000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Monthly PV Required for Payment
    |--------------------------------------------------------------------------
    */

    'monthly_pv_required' => [
        1 => 0,
        2 => 20,
        3 => 20,
        4 => 25,
        5 => 30,
        6 => 50,
        7 => 100,
        8 => 200,
        9 => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rank Conditions (Niveau 4 to 9)
    |--------------------------------------------------------------------------
    */

    'rank_conditions' => [
        4 => [
            [
                'type' => 'personal_pv',
                'value' => 1000,
                'description' => 'Personal PV >= 1000'
            ],
            [
                'type' => 'branches',
                'branches' => 3,
                'rank_level' => 3,
                'group_pv' => 1000,
                'description' => '3 branches level 3 with 1000 PV'
            ],
            [
                'type' => 'branches',
                'branches' => 2,
                'rank_level' => 3,
                'group_pv' => 2200,
                'description' => '2 branches level 3 with 2200 PV'
            ],
        ],
        5 => [
            [
                'type' => 'branches',
                'branches' => 3,
                'rank_level' => 4,
                'group_pv' => 3800,
                'description' => '3 branches level 4 with 3800 PV'
            ],
            [
                'type' => 'branches',
                'branches' => 2,
                'rank_level' => 4,
                'group_pv' => 7800,
                'description' => '2 branches level 4 with 7800 PV'
            ],
            [
                'type' => 'branches_mixed',
                'branches' => [2 => 4, 4 => 3],
                'group_pv' => 3800,
                'description' => '2 branches level 4 and 4 branches level 3 with 3800 PV'
            ],
            [
                'type' => 'branches_mixed',
                'branches' => [1 => 4, 6 => 3],
                'group_pv' => 3800,
                'description' => '1 branch level 4 and 6 branches level 3 with 3800 PV'
            ],
        ],
        6 => [
            [
                'type' => 'branches',
                'branches' => 3,
                'rank_level' => 5,
                'group_pv' => 16000,
                'description' => '3 branches level 5 with 16000 PV'
            ],
            [
                'type' => 'branches',
                'branches' => 2,
                'rank_level' => 5,
                'group_pv' => 35000,
                'description' => '2 branches level 5 with 35000 PV'
            ],
            [
                'type' => 'branches_mixed',
                'branches' => [2 => 5, 4 => 4],
                'group_pv' => 16000,
                'description' => '2 branches level 5 and 4 branches level 4 with 16000 PV'
            ],
            [
                'type' => 'branches_mixed',
                'branches' => [1 => 5, 6 => 4],
                'group_pv' => 16000,
                'description' => '1 branch level 5 and 6 branches level 4 with 16000 PV'
            ],
        ],
        7 => [
            [
                'type' => 'branches',
                'branches' => 3,
                'rank_level' => 6,
                'group_pv' => 73000,
                'description' => '3 branches level 6 with 73000 PV'
            ],
            [
                'type' => 'branches',
                'branches' => 2,
                'rank_level' => 6,
                'group_pv' => 145000,
                'description' => '2 branches level 6 with 145000 PV'
            ],
            [
                'type' => 'branches_mixed',
                'branches' => [2 => 6, 4 => 5],
                'group_pv' => 73000,
                'description' => '2 branches level 6 and 4 branches level 5 with 73000 PV'
            ],
            [
                'type' => 'branches_mixed',
                'branches' => [1 => 6, 6 => 5],
                'group_pv' => 73000,
                'description' => '1 branch level 6 and 6 branches level 5 with 73000 PV'
            ],
        ],
        8 => [
            [
                'type' => 'branches',
                'branches' => 3,
                'rank_level' => 7,
                'group_pv' => 280000,
                'description' => '3 branches level 7 with 280000 PV'
            ],
            [
                'type' => 'branches',
                'branches' => 2,
                'rank_level' => 7,
                'group_pv' => 580000,
                'description' => '2 branches level 7 with 580000 PV'
            ],
            [
                'type' => 'branches_mixed',
                'branches' => [2 => 7, 4 => 6],
                'group_pv' => 280000,
                'description' => '2 branches level 7 and 4 branches level 6 with 280000 PV'
            ],
            [
                'type' => 'branches_mixed',
                'branches' => [1 => 7, 6 => 6],
                'group_pv' => 280000,
                'description' => '1 branch level 7 and 6 branches level 6 with 280000 PV'
            ],
        ],
        9 => [
            [
                'type' => 'branches',
                'branches' => 3,
                'rank_level' => 8,
                'group_pv' => 400000,
                'description' => '3 branches level 8 with 400000 PV'
            ],
            [
                'type' => 'branches',
                'branches' => 2,
                'rank_level' => 8,
                'group_pv' => 780000,
                'description' => '2 branches level 8 with 780000 PV'
            ],
            [
                'type' => 'branches_mixed',
                'branches' => [2 => 8, 4 => 7],
                'group_pv' => 400000,
                'description' => '2 branches level 8 and 4 branches level 7 with 400000 PV'
            ],
            [
                'type' => 'branches_mixed',
                'branches' => [1 => 8, 6 => 7],
                'group_pv' => 400000,
                'description' => '1 branch level 8 and 6 branches level 7 with 400000 PV'
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Higher Ranks (Ruby, Sapphire, Diamonds, Shareholder)
    |--------------------------------------------------------------------------
    */

    'higher_ranks' => [
        'ruby' => [
            'name' => 'Ruby',
            'slug' => 'ruby',
            'level' => 1,
            'min_branches_rank_9' => 2,
            'global_bonus_percentage' => 2.5,
            'description' => 'At least 2 branches at Level 9',
        ],
        'sapphire' => [
            'name' => 'Sapphire',
            'slug' => 'sapphire',
            'level' => 2,
            'min_branches_rank_9' => 3,
            'global_bonus_percentage' => 1.0,
            'description' => 'At least 3 branches at Level 9',
        ],
        'diamond_1' => [
            'name' => 'Diamond 1',
            'slug' => 'diamond-1',
            'level' => 3,
            'min_branches_rank_9' => 4,
            'global_bonus_percentage' => 1.5,
            'description' => 'At least 4 branches at Level 9',
        ],
        'diamond_2' => [
            'name' => 'Diamond 2',
            'slug' => 'diamond-2',
            'level' => 4,
            'min_branches_rank_9' => 5,
            'global_bonus_percentage' => 1.4,
            'description' => 'At least 5 branches at Level 9',
        ],
        'diamond_3' => [
            'name' => 'Diamond 3',
            'slug' => 'diamond-3',
            'level' => 5,
            'min_branches_rank_9' => 6,
            'global_bonus_percentage' => 1.3,
            'description' => 'At least 6 branches at Level 9',
        ],
        'diamond_4' => [
            'name' => 'Diamond 4',
            'slug' => 'diamond-4',
            'level' => 6,
            'min_branches_rank_9' => 7,
            'global_bonus_percentage' => 1.2,
            'description' => 'At least 7 branches at Level 9',
        ],
        'diamond_5' => [
            'name' => 'Diamond 5',
            'slug' => 'diamond-5',
            'level' => 7,
            'min_branches_rank_9' => 8,
            'global_bonus_percentage' => 1.1,
            'description' => 'At least 8 branches at Level 9',
        ],
        'shareholder' => [
            'name' => 'Shareholder',
            'slug' => 'shareholder',
            'level' => 8,
            'min_branches_diamond' => 4,
            'global_bonus_percentage' => 1.0,
            'description' => 'At least 4 Diamond branches',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fees and Limits
    |--------------------------------------------------------------------------
    */

    'withdrawal_fee' => 2.5,
    'min_withdrawal' => 10,
    'max_withdrawal_per_day' => 5000,
    'max_commission_per_transaction' => 10000,

    /*
    |--------------------------------------------------------------------------
    | ✅ NOUVEAU: Tax Rate
    |--------------------------------------------------------------------------
    */

    'tax_rate' => env('COMMISSION_TAX_RATE', 5),

    /*
    |--------------------------------------------------------------------------
    | ✅ NOUVEAU: Minimum Payment Threshold
    |--------------------------------------------------------------------------
    */

    'min_payment' => env('COMMISSION_MIN_PAYMENT', 1),

    /*
    |--------------------------------------------------------------------------
    | ✅ NOUVEAU: Payment Validation Rules
    |--------------------------------------------------------------------------
    */

    'payment_validation' => [
        'require_kyc' => env('COMMISSION_REQUIRE_KYC', true),
        'require_monthly_pv' => env('COMMISSION_REQUIRE_MONTHLY_PV', true),
        'require_active_account' => env('COMMISSION_REQUIRE_ACTIVE_ACCOUNT', true),
        'require_rank' => env('COMMISSION_REQUIRE_RANK', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | ✅ NOUVEAU: Payment Status Messages
    |--------------------------------------------------------------------------
    */

    'payment_status_messages' => [
        'kyc_pending' => 'KYC non vérifié - paiement en attente de vérification',
        'monthly_pv_insufficient' => 'PV mensuel insuffisant ({pv} PV requis: {required} PV)',
        'amount_too_small' => 'Montant inférieur au minimum de paiement ({amount} < {min})',
        'account_inactive' => 'Compte inactif - paiement ignoré',
        'no_rank' => 'Aucun grade trouvé - paiement ignoré',
    ],

    /*
    |--------------------------------------------------------------------------
    | Unilevel Levels
    |--------------------------------------------------------------------------
    */

    'unilevel' => [
        'max_levels' => 9,
        'generations' => [
            1 => 'direct',
            2 => 'indirect',
            3 => 'leadership',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Commission Periods
    |--------------------------------------------------------------------------
    */

    'periods' => [
        'calculation_day' => 5,
        'payment_day' => 15,
        'closure_day' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Bonus Types
    |--------------------------------------------------------------------------
    */

    'bonus_types' => [
        'direct' => 'Direct Bonus',
        'indirect' => 'Indirect Bonus',
        'leadership' => 'Leadership Bonus',
        'retail' => 'Retail Profit',
        'consumer' => 'Consumer Bonus',
        'global' => 'Global Bonus',
    ],

    /*
    |--------------------------------------------------------------------------
    | ✅ NOUVEAU: Commission Types Configuration
    |--------------------------------------------------------------------------
    */

    'commission_types' => [
        'direct' => [
            'label' => 'Direct Bonus',
            'description' => 'Commission directe du parrain',
            'icon' => 'user-plus',
            'color' => 'primary',
        ],
        'indirect' => [
            'label' => 'Indirect Bonus',
            'description' => 'Commission indirecte des parrains supérieurs',
            'icon' => 'users',
            'color' => 'warning',
        ],
        'leadership' => [
            'label' => 'Leadership Bonus',
            'description' => 'Bonus de leadership pour les niveaux 5+',
            'icon' => 'crown',
            'color' => 'danger',
        ],
        'retail' => [
            'label' => 'Retail Profit',
            'description' => 'Profit sur la revente directe (25%)',
            'icon' => 'shopping-bag',
            'color' => 'success',
        ],
        'consumer' => [
            'label' => 'Consumer Bonus',
            'description' => 'Bonus consommateur sur les achats personnels (6%)',
            'icon' => 'gift',
            'color' => 'teal',
        ],
        'global' => [
            'label' => 'Global Bonus',
            'description' => 'Bonus mondial pour les grades supérieurs',
            'icon' => 'globe',
            'color' => 'info',
        ],
    ],
];