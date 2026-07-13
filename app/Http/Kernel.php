<?php
// app/Http/Kernel.php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // Trust Proxies
        \App\Http\Middleware\TrustProxies::class,
        
        // Handle CORS
        \Illuminate\Http\Middleware\HandleCors::class,
        
        // Prevent requests during maintenance
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        
        // Validate post size
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        
        // Trim strings
        \App\Http\Middleware\TrimStrings::class,
        
        // Convert empty strings to null
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\EnsureUserActive::class,
            \App\Http\Middleware\EnsureRankIsUpToDate::class, // ✅ AJOUTÉ
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\ForceJsonResponse::class,
            \App\Http\Middleware\ApiAuthenticate::class,
            \App\Http\Middleware\EnsureUserActive::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        // Laravel Default
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // ============================================================
        // CUSTOM MIDDLEWARES - SALANG MLM
        // ============================================================

        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'api.auth' => \App\Http\Middleware\ApiAuthenticate::class,
        'active' => \App\Http\Middleware\EnsureUserActive::class,
        'json' => \App\Http\Middleware\ForceJsonResponse::class,
        'kyc.verified' => \App\Http\Middleware\EnsureKycVerified::class,
        'maintenance' => \App\Http\Middleware\MaintenanceMode::class,
        'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
        'sanitize' => \App\Http\Middleware\SanitizeInput::class,
        'log.request' => \App\Http\Middleware\LogRequest::class,
        'cache.response' => \App\Http\Middleware\CacheResponse::class,
        'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        
        // ✅ NOUVEAU MIDDLEWARE
        'rank.update' => \App\Http\Middleware\EnsureRankIsUpToDate::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * @var array<int, class-string|string>
     */
    protected $middlewarePriority = [
        \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,

        \App\Http\Middleware\SecurityHeaders::class,
        \App\Http\Middleware\MaintenanceMode::class,
        \App\Http\Middleware\ApiAuthenticate::class,
        \App\Http\Middleware\AdminMiddleware::class,
        \App\Http\Middleware\EnsureUserActive::class,
        \App\Http\Middleware\EnsureKycVerified::class,
        \App\Http\Middleware\RateLimitMiddleware::class,
        \App\Http\Middleware\LogRequest::class,
        \App\Http\Middleware\EnsureRankIsUpToDate::class, // ✅ AJOUTÉ
    ];
}