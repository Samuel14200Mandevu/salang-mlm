<?php
// app/Providers/RouteServiceProvider.php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // ✅ Routes API avec préfixe api
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // ✅ Routes Web
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // ✅ Routes Auth (incluses dans web.php via require)
            // require base_path('routes/auth.php');

            // ✅ Routes Admin (déjà incluses dans web.php)
            // Pas besoin de charger séparément car elles sont dans web.php

            // ✅ Routes Webhook (dans web.php avec withoutMiddleware)
            // Pas besoin de fichier séparé
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // ✅ API - 60 requêtes par minute
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // ✅ Login - 5 tentatives par minute
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // ✅ Register - 3 inscriptions par minute
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // ✅ Webhook - 100 requêtes par minute (plus permissif)
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });

        // ✅ Withdrawal - 10 demandes par minute
        RateLimiter::for('withdrawal', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // ✅ Commission - 20 requêtes par minute
        RateLimiter::for('commission', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        // ✅ E-commerce - 30 requêtes par minute
        RateLimiter::for('ecommerce', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // ✅ Admin - 200 requêtes par minute
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(200)->by($request->user()?->id ?: $request->ip());
        });

        // ✅ Auth général - 10 requêtes par minute
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // ✅ Sécurité pour les endpoints sensibles
        RateLimiter::for('sensitive', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // ✅ File upload - 5 uploads par minute
        RateLimiter::for('upload', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // ✅ Paiements - 10 paiements par minute
        RateLimiter::for('payment', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // ✅ KYC - 3 soumissions par minute
        RateLimiter::for('kyc', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip());
        });
    }
}