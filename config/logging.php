<?php

return [
    'channels' => [
        'single' => [
            'driver' => 'single',
            'path' => env('LOG_STREAM_PATH', '/tmp/storage/logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
    ],
];