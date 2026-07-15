<?php
// app/Providers/AppServiceProvider.php

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
use App\Services\MLM\AdvancedRankCalculator;
use App\Services\MLM\RankConditionChecker;
use App\Services\MLM\CommissionDistributor;
use App\Services\MLM\MonthlyCommissionService;
use App\Models\User;
use App\Models\Order;
use App\Models\Genealogy;
use App\Observers\UserObserver;
use App\Observers\OrderObserver;
use App\Observers\GenealogyObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ✅ SERVICES MLM AVEC DÉPENDANCES
        $this->app->singleton(RankConditionChecker::class, function ($app) {
            return new RankConditionChecker();
        });

        $this->app->singleton(AdvancedRankCalculator::class, function ($app) {
            return new AdvancedRankCalculator(
                $app->make(RankConditionChecker::class)
            );
        });

        $this->app->singleton(CommissionDistributor::class, function ($app) {
            return new CommissionDistributor();
        });

        // ✅ MonthlyCommissionService avec ses dépendances
        $this->app->singleton(MonthlyCommissionService::class, function ($app) {
            return new MonthlyCommissionService(
                $app->make(AdvancedRankCalculator::class),
                $app->make(CommissionDistributor::class)
            );
        });

        // ✅ CommissionService AVEC SES 3 DÉPENDANCES
        $this->app->singleton(CommissionService::class, function ($app) {
            return new CommissionService(
                $app->make(AdvancedRankCalculator::class),
                $app->make(RankConditionChecker::class),
                $app->make(CommissionDistributor::class)
            );
        });

        // ✅ RankService (à garder pour compatibilité)
        $this->app->singleton(RankService::class, function ($app) {
            return new RankService();
        });

        // ✅ NetworkService
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

        // ✅ Enregistrement des commandes Artisan personnalisées
        $this->commands([
            \App\Console\Commands\ProcessMonthlyCommissions::class,
            \App\Console\Commands\UpdateRanks::class,
            \App\Console\Commands\CalculateCommissions::class,
            \App\Console\Commands\ProcessPendingWithdrawals::class,
            \App\Console\Commands\RecalculateAllRanks::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forcer HTTPS en production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Définir la longueur par défaut des chaînes (compatibilité MySQL)
        Schema::defaultStringLength(191);

        // Configurer les timeouts
        $this->configureTimeouts();

        // Configurer les middlewares
        $this->configureMiddleware();

        // ✅ ENREGISTRER LES OBSERVERS
        User::observe(UserObserver::class);
        Order::observe(OrderObserver::class);
        Genealogy::observe(GenealogyObserver::class);
    }

    /**
     * Configurer les timeouts
     */
    protected function configureTimeouts(): void
    {
        config(['app.request_timeout' => 120]);
    }

    /**
     * Configurer les middlewares
     */
    protected function configureMiddleware(): void
    {
        // Ajouter des middlewares personnalisés si nécessaire
    }
}