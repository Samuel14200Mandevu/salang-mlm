<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Passerelles de paiement
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'crypto' => [
            'enabled' => env('CRYPTO_ENABLED', true),
            'networks' => ['TRC20', 'ERC20', 'BEP20'],
            'default' => 'TRC20',
            'api_key' => env('CRYPTO_API_KEY', ''),
            'api_secret' => env('CRYPTO_API_SECRET', ''),
            'webhook_secret' => env('CRYPTO_WEBHOOK_SECRET', ''),
            'base_url' => env('CRYPTO_BASE_URL', 'https://api.coinbase.com/v2'),
        ],
        'mobile_money' => [
            'enabled' => env('MOBILE_MONEY_ENABLED', true),
            'providers' => ['Airtel Money', 'Orange Money', 'M-Pesa'],
            'default' => 'Orange Money',
            'api_key' => env('MOBILE_MONEY_API_KEY', ''),
            'api_secret' => env('MOBILE_MONEY_API_SECRET', ''),
            'base_url' => env('MOBILE_MONEY_BASE_URL', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Frais de transaction
    |--------------------------------------------------------------------------
    */
    'fees' => [
        'crypto' => env('CRYPTO_FEE', 0.5),
        'mobile_money' => env('MOBILE_MONEY_FEE', 1.5),
        'bank_transfer' => env('BANK_TRANSFER_FEE', 0.5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'crypto' => '/webhook/crypto',
        'mobile_money' => '/webhook/mobile-money',
    ],
];