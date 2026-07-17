<?php
// app/Console/Commands/RecalculateTeamPV.php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RecalculateTeamPV extends Command
{
    protected $signature = 'team:recalculate {--user= : ID de l\'utilisateur spécifique}';
    protected $description = 'Recalcule les PV d\'équipe pour tous les utilisateurs';

    public function handle()
    {
        $query = User::where('is_active', true);
        
        if ($this->option('user')) {
            $query->where('id', $this->option('user'));
        }
        
        $users = $query->get();
        $this->info("Recalcul des PV d'équipe pour {$users->count()} utilisateurs...");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        foreach ($users as $user) {
            // Vider le cache
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            
            // Recalculer le Team PV
            $teamPV = $this->calculateTeamPV($user);
            $teamBV = $this->calculateTeamBV($user);
            $totalTeam = $this->countDescendants($user);
            
            $user->team_pv = $teamPV;
            $user->team_bv = $teamBV;
            $user->total_team = $totalTeam;
            $user->saveQuietly();
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('✅ Recalcul terminé !');
    }

    private function calculateTeamPV(User $user): int
    {
        $total = $user->pv_balance ?? 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();
        
        foreach ($children as $child) {
            $total += $this->calculateTeamPV($child);
        }
        
        return $total;
    }

    private function calculateTeamBV(User $user): int
    {
        $total = $user->bv_balance ?? 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();
        
        foreach ($children as $child) {
            $total += $this->calculateTeamBV($child);
        }
        
        return $total;
    }

    private function countDescendants(User $user): int
    {
        $count = 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();
        
        foreach ($children as $child) {
            $count++;
            $count += $this->countDescendants($child);
        }
        
        return $count;
    }
}