<?php
// config/ranks.php

return [
    /*
    |--------------------------------------------------------------------------
    | Rank Configuration - Salang MLM Plan
    |--------------------------------------------------------------------------
    */

    'ranks' => [
        1 => [
            'name' => 'Distributor',
            'slug' => 'distributor',
            'level' => 1,
            'min_pv' => 0,
            'min_bv' => 0,
            'monthly_pv_required' => 0,
            'team_pv_required' => 0,
            'bonus_percentage' => 0,
            'description' => 'Starting level - 30$ registration',
            'icon' => 'level-1',
            'color' => 'gray',
            'is_active' => true,
        ],
        2 => [
            'name' => 'Qualification',
            'slug' => 'supervisor',
            'level' => 2,
            'min_pv' => 100,
            'min_bv' => 100,
            'monthly_pv_required' => 20,
            'team_pv_required' => 0,
            'bonus_percentage' => 6,
            'description' => '100 PV - 6% bonus',
            'icon' => 'level-2',
            'color' => 'blue',
            'is_active' => true,
        ],
        3 => [
            'name' => 'Assistant Manager',
            'slug' => 'assistant-manager',
            'level' => 3,
            'min_pv' => 200,
            'min_bv' => 200,
            'monthly_pv_required' => 20,
            'team_pv_required' => 0,
            'bonus_percentage' => 22,
            'description' => '200 PV - 22% bonus',
            'icon' => 'level-3',
            'color' => 'green',
            'is_active' => true,
        ],
        4 => [
            'name' => 'Manager',
            'slug' => 'manager',
            'level' => 4,
            'min_pv' => 1000,
            'min_bv' => 1000,
            'monthly_pv_required' => 25,
            'team_pv_required' => 0,
            'bonus_percentage' => 26,
            'description' => '1000 PV - 26% bonus',
            'icon' => 'level-4',
            'color' => 'green',
            'is_active' => true,
        ],
        5 => [
            'name' => 'Senior Manager',
            'slug' => 'senior-manager',
            'level' => 5,
            'min_pv' => 3800,
            'min_bv' => 3800,
            'monthly_pv_required' => 30,
            'team_pv_required' => 500,
            'bonus_percentage' => 30,
            'description' => '3800 PV - 30% bonus',
            'icon' => 'level-5',
            'color' => 'orange',
            'is_active' => true,
        ],
        6 => [
            'name' => 'Soaring Manager',
            'slug' => 'soaring-manager',
            'level' => 6,
            'min_pv' => 16000,
            'min_bv' => 16000,
            'monthly_pv_required' => 50,
            'team_pv_required' => 1000,
            'bonus_percentage' => 34,
            'description' => '16000 PV - 34% bonus',
            'icon' => 'level-6',
            'color' => 'orange',
            'is_active' => true,
        ],
        7 => [
            'name' => 'Sapphire Manager',
            'slug' => 'sapphire-manager',
            'level' => 7,
            'min_pv' => 73000,
            'min_bv' => 73000,
            'monthly_pv_required' => 100,
            'team_pv_required' => 2000,
            'bonus_percentage' => 40,
            'description' => '73000 PV - 40% bonus',
            'icon' => 'level-7',
            'color' => 'red',
            'is_active' => true,
        ],
        8 => [
            'name' => 'Blue Diamond',
            'slug' => 'blue-diamond',
            'level' => 8,
            'min_pv' => 280000,
            'min_bv' => 280000,
            'monthly_pv_required' => 200,
            'team_pv_required' => 3000,
            'bonus_percentage' => 43,
            'description' => '280000 PV - 43% bonus',
            'icon' => 'level-8',
            'color' => 'red',
            'is_active' => true,
        ],
        9 => [
            'name' => 'Diamond Pearl',
            'slug' => 'diamond-pearl',
            'level' => 9,
            'min_pv' => 400000,
            'min_bv' => 400000,
            'monthly_pv_required' => 300,
            'team_pv_required' => 5000,
            'bonus_percentage' => 45,
            'description' => '400000 PV - 45% bonus',
            'icon' => 'level-9',
            'color' => 'gold',
            'is_active' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Special Ranks (Above Level 9)
    |--------------------------------------------------------------------------
    */

    'special_ranks' => [
        'ruby' => [
            'name' => 'Ruby',
            'slug' => 'ruby',
            'level' => 1,
            'min_branches_rank_9' => 2,
            'global_bonus_percentage' => 2.5,
            'description' => 'At least 2 branches at Level 9',
            'icon' => 'ruby',
            'color' => 'red',
        ],
        'sapphire' => [
            'name' => 'Sapphire',
            'slug' => 'sapphire',
            'level' => 2,
            'min_branches_rank_9' => 3,
            'global_bonus_percentage' => 1.0,
            'description' => 'At least 3 branches at Level 9',
            'icon' => 'sapphire',
            'color' => 'blue',
        ],
        'diamond_1' => [
            'name' => 'Diamond 1',
            'slug' => 'diamond-1',
            'level' => 3,
            'min_branches_rank_9' => 4,
            'global_bonus_percentage' => 1.5,
            'description' => 'At least 4 branches at Level 9',
            'icon' => 'diamond',
            'color' => 'purple',
        ],
        'diamond_2' => [
            'name' => 'Diamond 2',
            'slug' => 'diamond-2',
            'level' => 4,
            'min_branches_rank_9' => 5,
            'global_bonus_percentage' => 1.4,
            'description' => 'At least 5 branches at Level 9',
            'icon' => 'diamond',
            'color' => 'purple',
        ],
        'diamond_3' => [
            'name' => 'Diamond 3',
            'slug' => 'diamond-3',
            'level' => 5,
            'min_branches_rank_9' => 6,
            'global_bonus_percentage' => 1.3,
            'description' => 'At least 6 branches at Level 9',
            'icon' => 'diamond',
            'color' => 'purple',
        ],
        'diamond_4' => [
            'name' => 'Diamond 4',
            'slug' => 'diamond-4',
            'level' => 6,
            'min_branches_rank_9' => 7,
            'global_bonus_percentage' => 1.2,
            'description' => 'At least 7 branches at Level 9',
            'icon' => 'diamond',
            'color' => 'purple',
        ],
        'diamond_5' => [
            'name' => 'Diamond 5',
            'slug' => 'diamond-5',
            'level' => 7,
            'min_branches_rank_9' => 9,
            'global_bonus_percentage' => 1.1,
            'description' => 'At least 9 branches at Level 9',
            'icon' => 'diamond',
            'color' => 'purple',
        ],
        'shareholder' => [
            'name' => 'Shareholder',
            'slug' => 'shareholder',
            'level' => 8,
            'min_branches_diamond' => 4,
            'global_bonus_percentage' => 1.0,
            'description' => 'At least 4 Diamond branches',
            'icon' => 'shareholder',
            'color' => 'gold',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Promotion Delay
    |--------------------------------------------------------------------------
    */

    'promotion_delay' => [
        1 => 'immediate',
        2 => 'immediate',
        3 => 'immediate',
        4 => 'immediate',
        5 => 'immediate',
        6 => 'immediate',
        7 => 'next_month',
        8 => 'next_month',
        9 => 'next_month',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rank Labels
    |--------------------------------------------------------------------------
    */

    'labels' => [
        'Distributor' => 'Distributor',
        'Qualification' => 'Qualification',
        'Assistant Manager' => 'Assistant Manager',
        'Manager' => 'Manager',
        'Senior Manager' => 'Senior Manager',
        'Soaring Manager' => 'Soaring Manager',
        'Sapphire Manager' => 'Sapphire Manager',
        'Blue Diamond' => 'Blue Diamond',
        'Diamond Pearl' => 'Diamond Pearl',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rank Colors (CSS classes)
    |--------------------------------------------------------------------------
    */

    'colors' => [
        'gray' => 'gray',
        'blue' => 'blue',
        'green' => 'green',
        'orange' => 'orange',
        'red' => 'red',
        'gold' => 'gold',
        'purple' => 'purple',
    ],
];