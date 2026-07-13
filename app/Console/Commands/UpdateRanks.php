<?php
// app/Console/Commands/UpdateRanks.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Rank;
use App\Models\RankHistory;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateRanks extends Command
{
    protected $signature = 'ranks:update 
                            {--user= : ID de l\'utilisateur spécifique}
                            {--all : Mettre à jour tous les utilisateurs}
                            {--period= : Période au format YYYY-MM}';
    
    protected $description = 'Mettre à jour les grades des utilisateurs';

    protected $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        parent::__construct();
        $this->rankCalculator = $rankCalculator;
    }

    public function handle()
    {
        $this->info('Mise à jour des grades...');

        $period = $this->option('period') ?? date('Y-m');

        if ($this->option('user')) {
            $user = User::find($this->option('user'));
            if (!$user) {
                $this->error('Utilisateur non trouvé');
                return 1;
            }
            $this->updateUserRank($user, $period);
            return 0;
        }

        if ($this->option('all')) {
            $this->updateAllRanks($period);
            return 0;
        }

        // Par défaut, mettre à jour les utilisateurs actifs
        $this->updateActiveRanks($period);
        return 0;
    }

    private function updateUserRank($user, $period)
    {
        try {
            $oldRankId = $user->rank_id;
            $oldRankName = $user->rank_name;

            $newRank = $this->rankCalculator->calculateAdvancedRank($user);

            if (!$newRank) {
                $this->warn("Aucun grade trouvé pour {$user->name}");
                return;
            }

            if ($newRank->id != $oldRankId) {
                $user->rank_id = $newRank->id;
                $user->rank = $newRank->name;
                $user->last_rank_update = now();
                $user->save();

                RankHistory::create([
                    'user_id' => $user->id,
                    'old_rank_id' => $oldRankId,
                    'new_rank_id' => $newRank->id,
                    'old_rank_name' => $oldRankName,
                    'new_rank_name' => $newRank->name,
                    'pv_at_time' => $user->pv_balance,
                    'bv_at_time' => $user->bv_balance,
                    'notes' => "Mise à jour mensuelle {$period}",
                ]);

                $this->info(" {$user->name}: {$oldRankName} → {$newRank->name}");
            } else {
                $this->line(" {$user->name}: Grade inchangé ({$newRank->name})");
            }

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour grade', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            $this->error("Erreur pour {$user->name}: {$e->getMessage()}");
        }
    }

    private function updateAllRanks($period)
    {
        $users = User::all();
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $updated = 0;
        $unchanged = 0;

        foreach ($users as $user) {
            $oldRankId = $user->rank_id;
            $newRank = $this->rankCalculator->calculateAdvancedRank($user);
            
            if ($newRank && $newRank->id != $oldRankId) {
                $this->updateUserRank($user, $period);
                $updated++;
            } else {
                $unchanged++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("{$updated} utilisateurs promus");
        $this->info(" {$unchanged} utilisateurs inchangés");
    }

    private function updateActiveRanks($period)
    {
        // Utilisateurs avec des commandes récentes
        $users = User::whereHas('orders', function($query) {
            $query->where('payment_status', 'completed');
        })->take(100)->get();

        if ($users->isEmpty()) {
            $this->info('Aucun utilisateur actif');
            return;
        }

        $this->info(" {$users->count()} utilisateurs actifs");
        $this->updateAllRanks($period);
    }
}