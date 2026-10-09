<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use App\Models\QuestionConversation;
use App\Services\CommissionService;
use App\Services\PaymentService;
use App\Services\NetworkService;
use App\Services\CryptoPaymentService;
use App\Services\MobileMoneyService;
use App\Services\ImageUploadService;
use App\Services\MLM\AdvancedRankCalculator;
use App\Services\MLM\RankConditionChecker;
use App\Services\MLM\TeamPVCalculator;
use App\Services\MLM\CommissionDistributor;
use App\Services\MLM\MonthlyCommissionService;
use App\Services\MLM\PeriodCommissionCalculator;  // ✅ AJOUT
use App\Services\MLM\PaymentEligibilityChecker;
use App\Services\MLM\CommissionHistoryPaymentSync;
use App\Models\User;
use App\Models\Order;
use App\Models\Genealogy;
use App\Models\PublicationMedia;
use App\Observers\GenealogyObserver;
use App\Observers\OrderObserver;
use App\Observers\PublicationMediaObserver;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ══════════════════════════════════════════════════════════════
        // ✅ SERVICES MLM AVEC DÉPENDANCES
        // ══════════════════════════════════════════════════════════════

        // ✅ TeamPVCalculator (service unique de calcul team_pv)
        $this->app->singleton(TeamPVCalculator::class, function ($app) {
            return new TeamPVCalculator();
        });

        // ✅ RankConditionChecker
        $this->app->singleton(RankConditionChecker::class, function ($app) {
            return new RankConditionChecker();
        });

        // ✅ AdvancedRankCalculator (2 dépendances)
        $this->app->singleton(AdvancedRankCalculator::class, function ($app) {
            return new AdvancedRankCalculator(
                $app->make(RankConditionChecker::class),
                $app->make(TeamPVCalculator::class)
            );
        });

        // ✅ CommissionDistributor
        $this->app->singleton(CommissionDistributor::class, function ($app) {
            return new CommissionDistributor();
        });

        // ✅ NOUVEAU : PeriodCommissionCalculator (service différé)
        $this->app->singleton(PeriodCommissionCalculator::class, function ($app) {
            return new PeriodCommissionCalculator();
        });

        // ✅ MonthlyCommissionService avec ses 3 dépendances
        $this->app->singleton(MonthlyCommissionService::class, function ($app) {
            return new MonthlyCommissionService(
                $app->make(AdvancedRankCalculator::class),
                $app->make(CommissionDistributor::class),
                $app->make(PeriodCommissionCalculator::class),
                $app->make(TeamPVCalculator::class),
                $app->make(PaymentEligibilityChecker::class),
                $app->make(CommissionHistoryPaymentSync::class)
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

        // ✅ NetworkService
        $this->app->singleton(NetworkService::class, function ($app) {
            return new NetworkService();
        });

        // ══════════════════════════════════════════════════════════════
        // ✅ SERVICES PAIEMENT
        // ══════════════════════════════════════════════════════════════

        $this->app->singleton(MobileMoneyService::class, function ($app) {
            return new MobileMoneyService();
        });

        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService(
                $app->make(MobileMoneyService::class)
            );
        });

        $this->app->singleton(CryptoPaymentService::class, function ($app) {
            return new CryptoPaymentService();
        });

        // ══════════════════════════════════════════════════════════════
        // ✅ SERVICES UTILITAIRES
        // ══════════════════════════════════════════════════════════════

        $this->app->singleton(ImageUploadService::class, function ($app) {
            return new ImageUploadService();
        });

        // ══════════════════════════════════════════════════════════════
        // ✅ COMMANDES ARTISAN PERSONNALISÉES
        // ══════════════════════════════════════════════════════════════

        $this->commands([
            \App\Console\Commands\ProcessMonthlyCommissions::class,
            \App\Console\Commands\UpdateRanks::class,
            \App\Console\Commands\CalculateCommissions::class,
            \App\Console\Commands\ProcessPendingWithdrawals::class,
            \App\Console\Commands\RecalculateAllRanks::class,
            \App\Console\Commands\FixTeamPVInconsistencies::class,

            // ✅ NOUVELLES COMMANDES (calendrier MLM 8→7)
            \App\Console\Commands\CalculatePeriodCommissions::class,
            \App\Console\Commands\GenerateCommissionPayments::class,
            \App\Console\Commands\ResetMonthlyPV::class,
            \App\Console\Commands\SyncCommissionsFromHistory::class,
            \App\Console\Commands\SimulateCommissionPayments::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        App::setLocale(config('app.locale', env('APP_LOCALE', 'fr')));

        Paginator::defaultView('vendor.pagination.salang');
        Paginator::defaultSimpleView('vendor.pagination.simple-salang');

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
        PublicationMedia::observe(PublicationMediaObserver::class);

        Route::bind('question', function (string $value) {
            return QuestionConversation::query()->findOrFail($value);
        });
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