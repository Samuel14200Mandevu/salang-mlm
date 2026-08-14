<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\RankUpdateService;
use Illuminate\Console\Command;

class UpdateMonthlyPV extends Command
{
    protected $signature = 'pv:update-monthly {--user= : ID de l utilisateur specifique}';
    protected $description = 'Mettre a jour les PV mensuels de tous les utilisateurs';

    public function handle(RankUpdateService $rankService)
    {
        $userId = $this->option('user');
        
        $query = User::where('is_active', true);
        
        if ($userId) {
            $query->where('id', $userId);
            $this->info('Mise a jour pour l utilisateur ID ' . $userId);
        } else {
            $this->info('Mise a jour des PV mensuels pour tous les utilisateurs...');
        }
        
        $users = $query->get();
        $bar = $this->output->createProgressBar($users->count());

        foreach ($users as $user) {
            $user->updateMonthlyPV();
            $rankService->triggerRankUpdate($user, 'monthly_pv_update');
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('PV mensuels mis a jour pour ' . $users->count() . ' utilisateurs');
    }
}