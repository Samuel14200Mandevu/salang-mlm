<?php
// config/payment.php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    */

    'gateways' => [
        'crypto' => [
            'enabled' => env('CRYPTO_ENABLED', true),
            'networks' => ['TRC20', 'ERC20', 'BEP20'],
            'default' => env('CRYPTO_DEFAULT_NETWORK', 'TRC20'),
            'api_key' => env('CRYPTO_API_KEY', ''),
            'api_secret' => env('CRYPTO_API_SECRET', ''),
            'webhook_secret' => env('CRYPTO_WEBHOOK_SECRET', ''),
            'base_url' => env('CRYPTO_BASE_URL', 'https://api.coinbase.com/v2'),
        ],
        'mobile_money' => [
            'enabled' => env('MOBILE_MONEY_ENABLED', true),
            'providers' => ['Airtel Money', 'Orange Money', 'M-Pesa'],
            'default' => env('MOBILE_MONEY_DEFAULT_PROVIDER', 'Orange Money'),
            'api_key' => env('MOBILE_MONEY_API_KEY', ''),
            'api_secret' => env('MOBILE_MONEY_API_SECRET', ''),
            'base_url' => env('MOBILE_MONEY_BASE_URL', ''),
        ],
        'bank_transfer' => [
            'enabled' => env('BANK_TRANSFER_ENABLED', true),
            'api_key' => env('BANK_TRANSFER_API_KEY', ''),
            'api_secret' => env('BANK_TRANSFER_API_SECRET', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Fees
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
        'bank_transfer' => '/webhook/bank-transfer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Limits
    |--------------------------------------------------------------------------
    */

    'limits' => [
        'min_deposit' => env('MIN_DEPOSIT', 1),
        'max_deposit' => env('MAX_DEPOSIT', 100000),
        'min_withdrawal' => env('MIN_WITHDRAWAL', 10),
        'max_withdrawal' => env('MAX_WITHDRAWAL', 5000),
        'daily_withdrawal_limit' => env('DAILY_WITHDRAWAL_LIMIT', 10000),
        'monthly_withdrawal_limit' => env('MONTHLY_WITHDRAWAL_LIMIT', 50000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currencies
    |--------------------------------------------------------------------------
    */

    'currencies' => [
        'default' => 'USD',
        'supported' => ['USD', 'EUR', 'GBP', 'CFA'],
    ],
];