<?php

/**
 * Config package crypto-pay/binancepay + alias Salang (BINANCE_PAY_*).
 */
return [
    'binance_service_base_url' => env('BINANCE_SERVICE_BASE_URL', 'https://bpay.binanceapi.com'),

    'binance_service_return_url' => env('BINANCE_SERVICE_RETURN_URL', env('APP_URL').'/payment/success'),

    'binance_service_cancel_url' => env('BINANCE_SERVICE_CANCEL_URL', env('APP_URL').'/payment/cancel'),

    'binance_order_expire_time' => (int) env('BINANCE_ORDER_EXPIRE_TIME', 55),

    'binance_service_webhook_url' => env(
        'BINANCE_SERVICE_WEBHOOK_URL',
        env('APP_URL').'/webhook/binancepay'
    ),

    // Alias prioritaire BINANCE_PAY_* (demandé Salang) → fallback noms package
    'binance_merchant_api_key' => env('BINANCE_PAY_API_KEY', env('BINANCE_MERCHANT_API_KEY')),

    'binance_merchant_secret_key' => env('BINANCE_PAY_SECRET', env('BINANCE_MERCHANT_SECRET_KEY')),

    'binance_merchant_id' => env('BINANCE_PAY_MERCHANT_ID'),

    'binance_terminal_type' => env('BINANCE_TERMINAL_TYPE', 'WEB'),

    'binance_currency' => env('BINANCE_PAY_CURRENCY', 'USDT'),

    'binance_goods_type' => '02',

    'binance_goods_category' => 'Z000',
];
