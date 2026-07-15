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
        
        // ✅ Security Headers - Ajoutés globalement
        \App\Http\Middleware\SecurityHeaders::class,
        
        // ✅ Sanitize Input - Ajouté globalement
        \App\Http\Middleware\SanitizeInput::class,
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
            
            // ✅ SALANG MLM MIDDLEWARES
            \App\Http\Middleware\EnsureUserActive::class,
            \App\Http\Middleware\EnsureRankIsUpToDate::class,
            \App\Http\Middleware\MaintenanceMode::class,
        ],

        'api' => [
            // Laravel Sanctum
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            
            // Rate Limiting
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            
            // Route Binding
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            
            // ✅ SALANG API MIDDLEWARES
            \App\Http\Middleware\ForceJsonResponse::class,
            \App\Http\Middleware\ApiAuthenticate::class,
            \App\Http\Middleware\EnsureUserActive::class,
            \App\Http\Middleware\LogRequest::class,
            \App\Http\Middleware\RateLimitMiddleware::class . ':100,1',
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
        // ============================================================
        // LARAVEL DEFAULT MIDDLEWARES
        // ============================================================
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
        // SALANG MLM CUSTOM MIDDLEWARES
        // ============================================================

        // 🔐 AUTHENTICATION & SECURITY
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'api.auth' => \App\Http\Middleware\ApiAuthenticate::class,
        'active' => \App\Http\Middleware\EnsureUserActive::class,
        'kyc.verified' => \App\Http\Middleware\EnsureKycVerified::class,
        'rank.update' => \App\Http\Middleware\EnsureRankIsUpToDate::class,

        // 🛡️ SECURITY & PROTECTION
        'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        'sanitize' => \App\Http\Middleware\SanitizeInput::class,
        'maintenance' => \App\Http\Middleware\MaintenanceMode::class,

        // 📊 PERFORMANCE & CACHE
        'cache.response' => \App\Http\Middleware\CacheResponse::class,
        'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,

        // 📝 LOGGING & DEBUG
        'log.request' => \App\Http\Middleware\LogRequest::class,
        'force.json' => \App\Http\Middleware\ForceJsonResponse::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array<int, class-string|string>
     */
    protected $middlewarePriority = [
        // ============================================================
        // LARAVEL DEFAULT - PRIORITY
        // ============================================================
        \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,

        // ============================================================
        // SALANG MLM - PRIORITY ORDER
        // ============================================================
        
        // 1. SECURITY (Highest Priority)
        \App\Http\Middleware\SecurityHeaders::class,
        \App\Http\Middleware\MaintenanceMode::class,
        
        // 2. AUTHENTICATION
        \App\Http\Middleware\ApiAuthenticate::class,
        \App\Http\Middleware\AdminMiddleware::class,
        
        // 3. USER VALIDATION
        \App\Http\Middleware\EnsureUserActive::class,
        \App\Http\Middleware\EnsureKycVerified::class,
        \App\Http\Middleware\EnsureRankIsUpToDate::class,
        
        // 4. PERFORMANCE
        \App\Http\Middleware\RateLimitMiddleware::class,
        \App\Http\Middleware\CacheResponse::class,
        
        // 5. LOGGING
        \App\Http\Middleware\LogRequest::class,
        \App\Http\Middleware\ForceJsonResponse::class,
        
        // 6. INPUT PROCESSING
        \App\Http\Middleware\SanitizeInput::class,
    ];

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}