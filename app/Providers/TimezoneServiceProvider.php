<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TimezoneServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Récupérer le fuseau horaire depuis .env
        $timezone = env('APP_TIMEZONE', 'Africa/Kinshasa');
        
        // Définir le fuseau horaire PHP
        date_default_timezone_set($timezone);
        
        // Définir le fuseau horaire Laravel
        config(['app.timezone' => $timezone]);
        
        // Définir également dans l'application
        $this->app['config']->set('app.timezone', $timezone);
    }
}