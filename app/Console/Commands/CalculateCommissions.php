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

    protected $description = 'Calculer les commissions pour les utilisateurs (legacy)';

    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        parent::__construct();
        $this->commissionService = $commissionService;
    }

    public function handle()
    {
        $this->info('🔄 Calcul des commissions (legacy)...');

        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('⚠️ Mode SIMULATION - Aucune modification');
        }

        if ($this->option('period')) {
            return $this->processByPeriod($this->option('period'), $isDryRun);
        }

        if ($this->option('user')) {
            $user = User::find($this->option('user'));
            if (!$user) {
                $this->error('❌ Utilisateur non trouvé');
                return 1;
            }
            return $this->processUser($user, $isDryRun);
        }

        if ($this->option('all')) {
            return $this->processAllUsers($isDryRun);
        }

        return $this->processPending($isDryRun);
    }

    private function processUser($user, $isDryRun = false)
    {
        $pending = Commission::where('user_id', $user->id)->where('status', 'pending')->get();

        if ($pending->isEmpty()) {
            $this->line("⏸️ Aucune commission en attente");
            return 0;
        }

        if ($isDryRun) {
            $this->line("   🔍 Simulation: {$pending->count()} commissions ({$pending->sum('amount')} USD)");
            return 0;
        }

        $this->calculateForUser($user);
        return 0;
    }

    private function processAllUsers($isDryRun = false)
    {
        $users = User::whereHas('commissions', fn($q) => $q->where('status', 'pending'))->get();

        if ($users->isEmpty()) {
            $this->info('⏸️ Aucune commission en attente');
            return 0;
        }

        if ($isDryRun) {
            $this->line("   🔍 Simulation: {$users->count()} utilisateurs");
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
        $this->info('✅ Terminé');
        return 0;
    }

    private function processPending($isDryRun = false)
    {
        return $this->processAllUsers($isDryRun);
    }

    private function processByPeriod($period, $isDryRun = false)
    {
        $users = User::whereHas('commissions', function ($q) use ($period) {
            $q->where('period', $period)->where('status', 'pending');
        })->get();

        if ($users->isEmpty()) {
            $this->info("⏸️ Aucune commission en attente pour {$period}");
            return 0;
        }

        if ($isDryRun) {
            $this->line("   🔍 Simulation: {$users->count()} utilisateurs pour {$period}");
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
        $this->info('✅ Terminé');
        return 0;
    }

    private function calculateForUser($user)
    {
        try {
            $pending = Commission::where('user_id', $user->id)->where('status', 'pending')->get();
            if ($pending->isEmpty()) return;

            $totalAmount = 0;

            foreach ($pending as $commission) {
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

            $this->commissionService->updateUserRank($user);
            $this->line("   ✅ {$user->name}: {$pending->count()} commission(s) - {$totalAmount} USD");

        } catch (\Exception $e) {
            Log::error('Erreur calcul commission', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
            $this->error("❌ Erreur pour {$user->name}: {$e->getMessage()}");
        }
    }
}
