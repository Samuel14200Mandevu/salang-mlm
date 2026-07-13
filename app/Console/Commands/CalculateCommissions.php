<?php
// app/Console/Commands/CalculateCommissions.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Commission;
use App\Services\CommissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateCommissions extends Command
{
    protected $signature = 'commissions:calculate 
                            {--user= : ID de l\'utilisateur spécifique}
                            {--all : Calculer pour tous les utilisateurs}
                            {--period= : Période au format YYYY-MM}';
    
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

        if ($this->option('user')) {
            $user = User::find($this->option('user'));
            if (!$user) {
                $this->error('❌ Utilisateur non trouvé');
                return 1;
            }
            $this->calculateForUser($user);
            return 0;
        }

        if ($this->option('all')) {
            $this->calculateForAllUsers();
            return 0;
        }

        // Par défaut, calculer pour les utilisateurs avec des commissions en attente
        $users = User::whereHas('commissions', function($query) {
            $query->where('status', 'pending');
        })->get();

        if ($users->isEmpty()) {
            $this->info('Aucune commission en attente');
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
        $this->info('Calcul terminé');
    }

    private function calculateForUser($user)
    {
        try {
            $pendingCommissions = Commission::where('user_id', $user->id)
                ->where('status', 'pending')
                ->get();

            foreach ($pendingCommissions as $commission) {
                $wallet = $user->wallet;
                if ($wallet) {
                    $wallet->balance += $commission->amount;
                    $wallet->save();

                    $commission->status = 'paid';
                    $commission->paid_at = now();
                    $commission->save();
                }
            }

            // Mettre à jour le grade
            $this->commissionService->updateUserRank($user);

        } catch (\Exception $e) {
            Log::error('Erreur calcul commission', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            $this->error("❌ Erreur pour {$user->name}: {$e->getMessage()}");
        }
    }

    private function calculateForAllUsers()
    {
        $users = User::all();
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $this->calculateForUser($user);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Calcul terminé pour tous les utilisateurs');
    }
}