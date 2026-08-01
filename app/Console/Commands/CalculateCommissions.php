<?php
// app/Console/Commands/CalculateCommissions.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Commission;
use App\Models\Wallet;
use App\Services\CommissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateCommissions extends Command
{
    protected $signature = 'commissions:calculate 
                            {--user= : ID de l\'utilisateur spécifique}
                            {--all : Calculer pour tous les utilisateurs}
                            {--period= : Période au format YYYY-MM}
                            {--dry-run : Simulation sans modification}';
    
    protected $description = 'Calculer les commissions pour les utilisateurs';

    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        parent::__construct();
        $this->commissionService = $commissionService;
    }

    public function handle()
    {
        $this->info('🔄 Calcul des commissions...');

        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('⚠️ Mode SIMULATION - Aucune modification');
        }

        // Cas 1: Période spécifique
        if ($this->option('period')) {
            $period = $this->option('period');
            $this->info("📊 Période: {$period}");
            return $this->processByPeriod($period, $isDryRun);
        }

        // Cas 2: Utilisateur spécifique
        if ($this->option('user')) {
            $user = User::find($this->option('user'));
            if (!$user) {
                $this->error('❌ Utilisateur non trouvé');
                return 1;
            }
            return $this->processUser($user, $isDryRun);
        }

        // Cas 3: Tous les utilisateurs
        if ($this->option('all')) {
            return $this->processAllUsers($isDryRun);
        }

        // Cas 4: Par défaut - Commissions en attente
        return $this->processPending($isDryRun);
    }

    private function processUser($user, $isDryRun = false)
    {
        $this->info("📊 Traitement pour {$user->name}...");
        
        $pendingCommissions = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->get();

        if ($pendingCommissions->isEmpty()) {
            $this->line("⏸️ Aucune commission en attente");
            return 0;
        }

        if ($isDryRun) {
            $this->line("   🔍 Simulation: {$pendingCommissions->count()} commissions à traiter");
            $total = $pendingCommissions->sum('amount');
            $this->line("   💰 Total: {$total} USD");
            return 0;
        }

        $this->calculateForUser($user);
        return 0;
    }

    private function processAllUsers($isDryRun = false)
    {
        $users = User::whereHas('commissions', function($query) {
            $query->where('status', 'pending');
        })->get();

        if ($users->isEmpty()) {
            $this->info('⏸️ Aucune commission en attente');
            return 0;
        }

        $this->info("📊 {$users->count()} utilisateurs avec commissions en attente");

        if ($isDryRun) {
            $total = Commission::where('status', 'pending')->sum('amount');
            $count = Commission::where('status', 'pending')->count();
            $this->line("   🔍 Simulation: {$count} commissions, {$total} USD");
            return 0;
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $this->calculateForUser($user);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Calcul terminé pour tous les utilisateurs');
        return 0;
    }

    private function processPending($isDryRun = false)
    {
        $users = User::whereHas('commissions', function($query) {
            $query->where('status', 'pending');
        })->get();

        if ($users->isEmpty()) {
            $this->info('⏸️ Aucune commission en attente');
            return 0;
        }

        $this->info("📊 {$users->count()} utilisateurs avec commissions en attente");

        if ($isDryRun) {
            $total = Commission::where('status', 'pending')->sum('amount');
            $this->line("   🔍 Simulation: {$total} USD à traiter");
            return 0;
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $this->calculateForUser($user);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Calcul terminé');
        return 0;
    }

    private function processByPeriod($period, $isDryRun = false)
    {
        $users = User::whereHas('commissions', function($query) use ($period) {
            $query->where('period', $period)
                  ->where('status', 'pending');
        })->get();

        if ($users->isEmpty()) {
            $this->info('⏸️ Aucune commission en attente pour cette période');
            return 0;
        }

        $this->info("📊 {$users->count()} utilisateurs pour la période {$period}");

        if ($isDryRun) {
            $total = Commission::where('period', $period)
                ->where('status', 'pending')
                ->sum('amount');
            $this->line("   🔍 Simulation: {$total} USD à traiter");
            return 0;
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $this->calculateForUser($user);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Calcul terminé pour la période');
        return 0;
    }

    private function calculateForUser($user)
    {
        try {
            $pendingCommissions = Commission::where('user_id', $user->id)
                ->where('status', 'pending')
                ->get();

            if ($pendingCommissions->isEmpty()) {
                return;
            }

            $totalAmount = 0;

            foreach ($pendingCommissions as $commission) {
                $wallet = $user->wallet;
                if (!$wallet) {
                    $wallet = Wallet::create([
                        'user_id' => $user->id,
                        'balance' => 0,
                        'currency' => 'USD',
                    ]);
                }

                $wallet->balance += $commission->amount;
                $wallet->save();

                $commission->status = 'paid';
                $commission->paid_at = now();
                $commission->save();

                $totalAmount += $commission->amount;
            }

            // Mettre à jour le grade
            $this->commissionService->updateUserRank($user);

            $this->line("   ✅ {$user->name}: {$pendingCommissions->count()} commission(s) - {$totalAmount} USD");

        } catch (\Exception $e) {
            Log::error('Erreur calcul commission', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            $this->error("❌ Erreur pour {$user->name}: {$e->getMessage()}");
        }
    }
}