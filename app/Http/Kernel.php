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
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
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

        // Admin middleware - Restrict access to admin only
        'admin' => \App\Http\Middleware\AdminMiddleware::class,

        // API authentication middleware
        'api.auth' => \App\Http\Middleware\ApiAuthenticate::class,

        // Ensure user is active
        'active' => \App\Http\Middleware\EnsureUserActive::class,

        // Force JSON response for API requests
        'json' => \App\Http\Middleware\ForceJsonResponse::class,

        // Ensure KYC is verified
        'kyc.verified' => \App\Http\Middleware\EnsureKycVerified::class,

        // Maintenance mode
        'maintenance' => \App\Http\Middleware\MaintenanceMode::class,

        // Rate limiting
        'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,

        // Sanitize input
        'sanitize' => \App\Http\Middleware\SanitizeInput::class,

        // Log requests (for debugging)
        'log.request' => \App\Http\Middleware\LogRequest::class,

        // Cache response
        'cache.response' => \App\Http\Middleware\CacheResponse::class,

        // Security headers
        'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array<int, class-string|string>
     */
    protected $middlewarePriority = [
        // Laravel Default
        \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,

        // Custom Priority
        \App\Http\Middleware\SecurityHeaders::class,
        \App\Http\Middleware\MaintenanceMode::class,
        \App\Http\Middleware\ApiAuthenticate::class,
        \App\Http\Middleware\AdminMiddleware::class,
        \App\Http\Middleware\EnsureUserActive::class,
        \App\Http\Middleware\EnsureKycVerified::class,
        \App\Http\Middleware\RateLimitMiddleware::class,
        \App\Http\Middleware\LogRequest::class,
    ];
}