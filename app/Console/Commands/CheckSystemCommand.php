<?php
// app/Console/Commands/CheckSystem.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CheckSystem extends Command
{
    protected $signature = 'system:check';
    protected $description = 'Vérifier l\'état du système';

    public function handle()
    {
        $this->info('VÉRIFICATION DU SYSTÈME');
        $this->newLine();

        // Vérifier la base de données
        $this->line('Base de données :');
        try {
            DB::connection()->getPdo();
            $this->info('Connexion OK');
        } catch (\Exception $e) {
            $this->error('Connexion échouée');
        }

        // Vérifier le cache
        $this->line('Cache :');
        try {
            Cache::put('test', 'ok', 10);
            $this->info('Cache OK');
        } catch (\Exception $e) {
            $this->error('Cache échoué');
        }

        // Vérifier les utilisateurs
        $this->line('Utilisateurs :');
        $users = DB::table('users')->count();
        $this->info("{$users} utilisateurs");

        // Vérifier les commissions
        $this->line('Commissions :');
        $commissions = DB::table('commissions')->count();
        $pending = DB::table('commissions')->where('status', 'pending')->count();
        $this->info("{$commissions} commissions (dont {$pending} en attente)");

        // Vérifier les retraits
        $this->line('Retraits :');
        $withdrawals = DB::table('withdrawals')->count();
        $pendingWithdrawals = DB::table('withdrawals')->where('status', 'pending')->count();
        $this->info("{$withdrawals} retraits (dont {$pendingWithdrawals} en attente)");

        // Vérifier les jobs
        $this->line('Jobs :');
        $jobs = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')->count();
        $this->info("{$jobs} jobs en attente, {$failed} échoués");

        $this->newLine();
        $this->info('Vérification terminée');

        return 0;
    }
}