<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable PWA
    |--------------------------------------------------------------------------
    */
    'enable_pwa' => true,

    /*
    |--------------------------------------------------------------------------
    | Livewire Support
    |--------------------------------------------------------------------------
    */
    'livewire-app' => true,

    /*
    |--------------------------------------------------------------------------
    | Small Device Position
    |--------------------------------------------------------------------------
    */
    'small_device_position' => 'fixed',

    /*
    |--------------------------------------------------------------------------
    | Manifest Configuration
    |--------------------------------------------------------------------------
    */
    'manifest' => [
        'name' => 'Salang MLM',
        'short_name' => 'Salang',
        'description' => 'Salang Multi-Level Marketing Platform',
        'theme_color' => '#6366f1',
        'background_color' => '#ffffff',
        'display' => 'standalone',
        'orientation' => 'portrait',
        'scope' => '/',
        'start_url' => '/',
        'dir' => 'ltr',
        'lang' => 'fr',
        'icons' => [
            [
                'src' => '/favicon-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ],
            [
                'src' => '/favicon-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ],
            [
                'src' => '/apple-touch-icon.png',
                'sizes' => '180x180',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Worker Configuration
    |--------------------------------------------------------------------------
    */
    'service_worker' => [
        'enabled' => true,
        'filename' => 'sw.js',
        'minify' => true,
        'precache' => [
            '/',
            '/css/app.css',
            '/js/app.js',
            '/favicon.ico',
            '/favicon-192x192.png',
            '/favicon-512x512.png',
            '/apple-touch-icon.png',
        ],
        'cache' => [
            'strategy' => 'cacheFirst',
            'max_entries' => 100,
            'max_age_seconds' => 3600,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Offline Fallback
    |--------------------------------------------------------------------------
    */
    'offline_fallback' => [
        'enabled' => true,
        'view' => 'pwa::offline',
    ],
];
