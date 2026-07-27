<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\UpdateRanks;
use App\Jobs\UpdateTeamPV;
use App\Jobs\CalculatePVBV;
use App\Jobs\ProcessMonthlyCommissions;
use App\Jobs\UpdateCumulativePV;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // ============================================================
        // COMMISSIONS
        // ============================================================
        \App\Console\Commands\CalculateCommissions::class,
        \App\Console\Commands\ProcessCommissions::class,
        \App\Console\Commands\ProcessPendingWithdrawals::class,
        \App\Console\Commands\ProcessMonthlyCommissions::class,
        
        // ============================================================
        // GRADES (RANKS)
        // ============================================================
        \App\Console\Commands\UpdateRanks::class,
        \App\Console\Commands\RecalculateAllRanks::class,
        \App\Console\Commands\FixAllRanks::class,
        \App\Console\Commands\ForceUpdateRanks::class,
        \App\Console\Commands\RecalculateTeamPV::class,
        
        // ============================================================
        // PV & MISE À JOUR
        // ============================================================
        \App\Console\Commands\UpdateMonthlyPV::class,
        \App\Console\Commands\RecalculateMonthlyPV::class,
        \App\Console\Commands\ResetMonthlyPV::class,
        
        // ============================================================
        // RAPPORTS & MAINTENANCE
        // ============================================================
        \App\Console\Commands\GenerateReport::class,
        \App\Console\Commands\CleanLogs::class,
        \App\Console\Commands\SyncHigherRanks::class,
        \App\Console\Commands\CheckPackageExpiry::class,
        \App\Console\Commands\SendPendingNotifications::class,
        \App\Console\Commands\BackupRun::class,
        \App\Console\Commands\StatusCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ============================================================
        // RÉINITIALISATION DES PV MENSUELS - LE 7 DE CHAQUE MOIS
        // ============================================================
        
        // Réinitialiser les PV mensuels à 0 le 7 de chaque mois à 00:00
        $schedule->command('pv:reset-monthly')
            ->monthlyOn(7, '00:00')
            ->before(function () {
                \Log::info('🔄 Début de la réinitialisation des PV mensuels');
            })
            ->after(function () {
                \Log::info('✅ Réinitialisation des PV mensuels terminée');
            })
            ->onFailure(function () {
                \Log::error('❌ Échec de la réinitialisation des PV mensuels');
                // Envoyer une notification d'erreur
            })
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/pv-reset-monthly.log'));

        // Recalculer les PV mensuels après réinitialisation (5 minutes après)
        $schedule->command('monthly:recalculate')
            ->monthlyOn(7, '00:05')
            ->after(function () {
                \Log::info('✅ Recalcul des PV mensuels après réinitialisation terminé');
            })
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/pv-recalculate.log'));

        // Mettre à jour les grades après réinitialisation (15 minutes après)
        // Note: Les grades sont basés sur PV cumulé (pv_balance + team_pv)
        // Le reset ne touche pas aux grades, mais on vérifie quand même
        $schedule->command('ranks:update --all')
            ->monthlyOn(7, '00:15')
            ->after(function () {
                \Log::info('✅ Mise à jour des grades après réinitialisation des PV terminée');
            })
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/ranks-after-pv-reset.log'));

        // ============================================================
        // GRADES (RANKS) - PRIORITAIRE
        // ============================================================

        // Toutes les 5 minutes - Mettre à jour les grades en temps réel
        $schedule->job(new UpdateRanks())->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ranks-realtime.log'));

        // Toutes les 5 minutes - Mettre à jour les Team PV
        $schedule->job(new UpdateTeamPV())->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/team-pv.log'));

        // Toutes les 15 minutes - Calculer les PV/BV
        $schedule->job(new CalculatePVBV())->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/pv-calculate.log'));

        // Toutes les heures - Mettre à jour les PV cumulés
        $schedule->job(new UpdateCumulativePV())->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/pv-cumulative.log'));

        // Chaque jour à 00:30 - Forcer la mise à jour de tous les grades
        // Note: Décalé à 00:30 pour éviter conflit avec le reset du 7 à 00:00
        $schedule->command('ranks:update --all')->dailyAt('00:30')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/ranks-daily.log'));

        // Chaque jour à 00:45 - Corriger tous les grades
        // Note: Décalé à 00:45 pour éviter conflit avec le reset du 7
        $schedule->command('ranks:fix-all')->dailyAt('00:45')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/ranks-fix.log'));

        // Chaque jour à 01:00 - Recalculer tous les Team PV
        $schedule->command('team:recalculate')->dailyAt('01:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/team-recalculate.log'));

        // Chaque jour à 03:00 - Mettre à jour les PV mensuels
        $schedule->command('pv:update-monthly')->dailyAt('03:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/pv-monthly.log'));

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

        // Chaque heure - Statut du système
        $schedule->command('mlm:status')->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/status.log'));

        // ============================================================
        // VÉRIFICATION DES PV MENSUELS - JOURNALIER
        // ============================================================

        // Chaque jour à 23:55 - Vérifier l'état des PV mensuels
        $schedule->command('pv:check-status')->dailyAt('23:55')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/pv-check-status.log'));
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