<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// ============================================================
// CONFIGURATION POUR LARAVEL CLOUD
// ============================================================

// Optimisation des logs
putenv('LOG_CHANNEL=' . (app()->environment('local') ? 'stack' : 'null'));

// Désactiver DebugBar en production
if (!app()->environment('local')) {
    putenv('DEBUGBAR_ENABLED=false');
}

// Cache en mémoire (/tmp)
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
putenv('LOG_STREAM_PATH=/tmp/storage/logs/laravel.log');

// Forcer HTTPS
if (!app()->environment('local')) {
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $_SERVER['HTTPS'] = 'on';
        putenv('APP_URL=https://' . ($_SERVER['HTTP_HOST'] ?? ''));
    }
}

// ============================================================
// CONFIGURATION DE L'APPLICATION
// ============================================================

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middlewares personnalisés
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'json' => \App\Http\Middleware\ForceJsonResponse::class,
            'api.auth' => \App\Http\Middleware\ApiAuthenticate::class,
        ]);
        
        // Ajouter les middlewares aux groupes
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        
        // Exceptions CSRF
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gestion des erreurs JSON pour l'API
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson() || $request->ajax();
        });
        
        // Personnaliser les réponses d'erreur
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Une erreur est survenue',
                    'code' => $statusCode,
                ], $statusCode);
            }
            
            return null;
        });
    })
    ->create();