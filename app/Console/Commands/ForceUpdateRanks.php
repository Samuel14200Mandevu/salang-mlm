<?php
// app/Console/Commands/ForceUpdateRanks.php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;

class ForceUpdateRanks extends Command
{
    protected $signature = 'ranks:force-update {--user= : ID de l\'utilisateur spécifique}';
    protected $description = 'Force le calcul des grades pour tous les utilisateurs';

    public function handle(AdvancedRankCalculator $rankCalculator)
    {
        $query = User::where('is_active', true);

        if ($this->option('user')) {
            $query->where('id', $this->option('user'));
        }

        $users = $query->get();
        $this->info("Calcul des grades pour {$users->count()} utilisateurs...");

        $updated = 0;

        foreach ($users as $user) {
            $newRank = $rankCalculator->calculateAdvancedRank($user);
            
            if ($newRank && $newRank->id != $user->rank_id) {
                $oldRank = $user->rank_name;
                
                $user->rank_id = $newRank->id;
                $user->rank = $newRank->name;
                $user->rank_level = $newRank->level;
                $user->last_rank_update = now();
                $user->saveQuietly();
                
                $updated++;
                $this->line("✅ {$user->name}: {$oldRank} → {$newRank->name}");
            }
        }

        $this->info("✅ {$updated} utilisateurs mis à jour !");
    }
}