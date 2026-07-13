<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commandes existantes
        \App\Console\Commands\CalculateCommissions::class,
        \App\Console\Commands\ProcessCommissions::class,
        \App\Console\Commands\ProcessPendingWithdrawals::class,
        \App\Console\Commands\ProcessMonthlyCommissions::class,
        \App\Console\Commands\RecalculateAllRanks::class,
        \App\Console\Commands\UpdateRanks::class,
        
        // ✅ NOUVELLES COMMANDES - AJOUTER CES LIGNES
        \App\Console\Commands\UpdateMonthlyPV::class,
        \App\Console\Commands\GenerateReport::class,
        \App\Console\Commands\CleanLogs::class,
        \App\Console\Commands\SyncHigherRanks::class,
        \App\Console\Commands\CheckPackageExpiry::class,
        \App\Console\Commands\SendPendingNotifications::class,
        \App\Console\Commands\BackupRun::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ============================================================
        // COMMISSIONS
        // ============================================================

        // Toutes les 5 minutes - Traiter les commissions en attente
        $schedule->command('commissions:process')->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/commissions-process.log'));

        // Chaque jour à 01:00 - Calculer les commissions
        $schedule->command('commissions:calculate --all')->dailyAt('01:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/commissions-calculate.log'));

        // Chaque mois le 1er à 02:00 - Traitement mensuel MLM
        $schedule->command('mlm:process-monthly --steps=all')->monthlyOn(1, '02:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/mlm-monthly.log'));

        // ============================================================
        // GRADES (RANKS)
        // ============================================================

        // Toutes les 5 minutes - Recalculer les grades (temps réel)
        $schedule->command('ranks:recalculate')->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ranks-realtime.log'));

        // Chaque jour à 00:00 - Mettre à jour tous les grades
        $schedule->command('ranks:update --all')->dailyAt('00:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/ranks-daily.log'));

        // Chaque jour à 03:00 - Mettre à jour les PV mensuels
        $schedule->command('pv:update-monthly')->dailyAt('03:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/pv-update.log'));

        // ============================================================
        // RETRAITS (WITHDRAWALS)
        // ============================================================

        // Toutes les 15 minutes - Traiter les retraits en attente
        $schedule->command('withdrawals:process')->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/withdrawals.log'));

        // ============================================================
        // GRADES SUPÉRIEURS
        // ============================================================

        // Chaque jour à 05:00 - Synchroniser les grades supérieurs
        $schedule->command('higher-ranks:sync')->dailyAt('05:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/higher-ranks.log'));

        // ============================================================
        // MAINTENANCE & NETTOYAGE
        // ============================================================

        // Chaque jour à 04:00 - Nettoyer les logs
        $schedule->command('logs:clean --days=7')->dailyAt('04:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/cleanup.log'));

        // Chaque jour à 05:30 - Backup de la base
        $schedule->command('backup:run')->dailyAt('05:30')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/backup.log'));

        // Chaque jour à 06:00 - Générer le rapport
        $schedule->command('report:generate')->dailyAt('06:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/report.log'));

        // Chaque heure - Vérifier les expirations
        $schedule->command('packages:check-expiry')->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/packages.log'));

        // Chaque heure - Envoyer les notifications
        $schedule->command('notifications:send-pending')->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/notifications.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}