<?php

return [
    'enable_pwa' => env('PWA_ENABLED', true),
    'livewire-app' => true,
    'small_device_position' => 'fixed',

    'manifest' => [
        'name' => 'Salang MLM',  // ← Nom complet CORRECT
        'short_name' => 'Salang',  // ← Nom court CORRECT
        'description' => 'Salang Multi-Level Marketing Platform',
        'theme_color' => '#1f33cf',
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

    'offline_fallback' => [
        'enabled' => true,
        'view' => 'pwa::offline',
    ],
];