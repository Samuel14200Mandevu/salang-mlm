<?php

return [
    'ranks' => [
        'distributor' => [
            'name' => 'Distributor',
            'min_pv' => 0,
            'bonus_percentage' => 0,
            'description' => 'Niveau de départ',
        ],
        'supervisor' => [
            'name' => 'Supervisor',
            'min_pv' => 100,
            'bonus_percentage' => 5,
            'description' => '5% de bonus supplémentaire',
        ],
        'assistant-manager' => [
            'name' => 'Assistant Manager',
            'min_pv' => 200,
            'bonus_percentage' => 10,
            'description' => '10% de bonus supplémentaire',
        ],
        'manager' => [
            'name' => 'Manager',
            'min_pv' => 500,
            'bonus_percentage' => 15,
            'description' => '15% de bonus supplémentaire',
        ],
        'senior-manager' => [
            'name' => 'Senior Manager',
            'min_pv' => 1000,
            'bonus_percentage' => 20,
            'description' => '20% de bonus supplémentaire',
        ],
        'soaring-manager' => [
            'name' => 'Soaring Manager',
            'min_pv' => 2000,
            'bonus_percentage' => 25,
            'description' => '25% de bonus supplémentaire',
        ],
        'sapphire-manager' => [
            'name' => 'Sapphire Manager',
            'min_pv' => 5000,
            'bonus_percentage' => 30,
            'description' => '30% de bonus supplémentaire',
        ],
        'blue-diamond' => [
            'name' => 'Blue Diamond',
            'min_pv' => 10000,
            'bonus_percentage' => 35,
            'description' => '35% de bonus supplémentaire',
        ],
        'diamond' => [
            'name' => 'Diamond',
            'min_pv' => 20000,
            'bonus_percentage' => 40,
            'description' => '40% de bonus supplémentaire',
        ],
        'pearl' => [
            'name' => 'Pearl',
            'min_pv' => 50000,
            'bonus_percentage' => 50,
            'description' => '50% de bonus supplémentaire',
        ],
    ],
];