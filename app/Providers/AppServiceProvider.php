<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CommissionService;
use App\Services\PaymentService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Enregistrer les services
        $this->app->singleton(CommissionService::class, function ($app) {
            return new CommissionService();
        });

        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
