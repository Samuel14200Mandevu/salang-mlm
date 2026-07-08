<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use App\Services\CommissionService;
use App\Services\PaymentService;
use App\Services\RankService;
use App\Services\NetworkService;
use App\Services\CryptoPaymentService;
use App\Services\MobileMoneyService;
use App\Services\ImageUploadService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ✅ Services MLM
        $this->app->singleton(CommissionService::class, function ($app) {
            return new CommissionService();
        });

        $this->app->singleton(RankService::class, function ($app) {
            return new RankService();
        });

        $this->app->singleton(NetworkService::class, function ($app) {
            return new NetworkService();
        });

        // ✅ Services Paiement
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService();
        });

        $this->app->singleton(CryptoPaymentService::class, function ($app) {
            return new CryptoPaymentService();
        });

        $this->app->singleton(MobileMoneyService::class, function ($app) {
            return new MobileMoneyService();
        });

        // ✅ Services Utilitaires
        $this->app->singleton(ImageUploadService::class, function ($app) {
            return new ImageUploadService();
        });

        // ✅ Pour les environnements de développement
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ Forcer HTTPS en production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // ✅ Définir la longueur par défaut des chaînes (compatibilité MySQL)
        Schema::defaultStringLength(191);

        // ✅ Configurer les timeouts
        $this->configureTimeouts();

        // ✅ Configurer les middlewares
        $this->configureMiddleware();
    }

    /**
     * Configurer les timeouts
     */
    protected function configureTimeouts(): void
    {
        // Pour les requêtes longues (commissions, rapports)
        config(['app.request_timeout' => 120]);
    }

    /**
     * Configurer les middlewares
     */
    protected function configureMiddleware(): void
    {
        // Ajouter des middlewares personnalisés si nécessaire
        // $this->app['router']->pushMiddlewareToGroup('api', \App\Http\Middleware\ApiLogger::class);
    }
}