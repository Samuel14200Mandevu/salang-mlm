<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// ============================================================
// OPTIMISATIONS POUR LARAVEL CLOUD / PRODUCTION
// ============================================================

putenv('LOG_CHANNEL=null');

if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
    putenv('DEBUGBAR_ENABLED=false');
}

putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
putenv('LOG_STREAM_PATH=/tmp/storage/logs/laravel.log');

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    if (isset($_SERVER['HTTP_HOST'])) {
        putenv('APP_URL=https://' . $_SERVER['HTTP_HOST']);
    }
    $_SERVER['HTTPS'] = 'on';
}

// ============================================================
// CONFIGURATION DE L'APPLICATION
// ============================================================

return Application::configure(basePath: dirname(__DIR__))
    
    // ✅ ROUTING
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    
    // ✅ MIDDLEWARES
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ Enregistrer TOUS les alias de middlewares
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'json' => \App\Http\Middleware\ForceJsonResponse::class,
            'api.auth' => \App\Http\Middleware\ApiAuthenticate::class,
            'active' => \App\Http\Middleware\EnsureUserActive::class,  // <-- AJOUTER ICI
            'kyc.verified' => \App\Http\Middleware\EnsureKycVerified::class, // Optionnel
        ]);
        
        // ✅ Exceptions CSRF pour les webhooks
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
            'webhook/crypto',
            'webhook/mobile-money',
            'webhook/payment',
            'webhook/stripe',
            'webhook/coinbase',
        ]);
    })
    
    // ✅ EXCEPTIONS
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson() || $request->ajax();
        });
        
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = $e->getMessage() ?: 'Une erreur est survenue';
                
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error' => class_basename($e),
                ], $statusCode);
            }
            return null;
        });
    })
    
    // ✅ CRÉER L'APPLICATION
    ->create();