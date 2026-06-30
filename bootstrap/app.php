<?php

// Forcer les logs vers null dès le début
putenv('LOG_CHANNEL=null');
putenv('APP_DEBUG=false');
putenv('DEBUGBAR_ENABLED=false');

// Rediriger le cache vers /tmp/
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
putenv('LOG_STREAM_PATH=/tmp/storage/logs/laravel.log');
$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware alias
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
