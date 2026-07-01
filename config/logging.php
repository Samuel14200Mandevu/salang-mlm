<?php

use Monolog\Handler\NullHandler;

return [
    'default' => env('LOG_CHANNEL', 'null'),

    'channels' => [
        'null' => [
            'driver' => 'null',
        ],
    ],
];