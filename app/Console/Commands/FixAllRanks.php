<?php
// app/Console/Commands/FixAllRanks.php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;

class FixAllRanks extends Command
{
    protected $signature = 'ranks:fix-all {--dry-run : Simulation sans modification}';
    protected $description = 'Corrige tous les grades avec le calcul CUMUL = PV Personnel + Team PV';

    public function handle(AdvancedRankCalculator $calculator)
    {
        $dryRun = $this->option('dry-run');
        
        $users = User::where('is_active', true)->get();
        $this->info("Traitement de {$users->count()} utilisateurs...");
        
        if ($dryRun) {
            $this->warn("Mode SIMULATION - Aucune modification ne sera faite");
        }
        
        $results = [];
        $updated = 0;
        
        foreach ($users as $user) {
            // 1. Recalculer le Team PV
            $teamPV = $this->calculateTeamPV($user);
            $cumulPV = $user->pv_balance + $teamPV;
            
            // 2. Compter les branches qualifiées
            $branches = $this->countAllBranches($user);
            
            // 3. Calculer le nouveau grade
            if (!$dryRun) {
                $user->team_pv = $teamPV;
                $user->qualified_branches = $branches['total'];
                $user->saveQuietly();
            }
            
            $newRank = $calculator->calculateAdvancedRank($user);
            $currentRank = $user->rankObject;
            
            $results[] = [
                'id' => $user->id,
                'name' => $user->name,
                'current_rank' => $currentRank?->name ?? 'Distributeur',
                'current_level' => $currentRank?->level ?? 1,
                'pv_personnel' => $user->pv_balance,
                'team_pv' => $teamPV,
                'cumul_pv' => $cumulPV,
                'branches_niv3' => $branches['niv3'],
                'branches_niv4' => $branches['niv4'],
                'branches_niv5' => $branches['niv5'],
                'branches_niv6' => $branches['niv6'],
                'new_rank' => $newRank ? $newRank->name : 'Aucun',
                'new_level' => $newRank ? $newRank->level : 0,
                'will_update' => ($newRank && $newRank->id != ($currentRank?->id ?? 0)) ? 'YES' : 'NO',
            ];
            
            // Mettre à jour si nécessaire
            if (!$dryRun && $newRank && $newRank->id != ($currentRank?->id ?? 0)) {
                $oldRank = $currentRank?->name ?? 'Distributeur';
                $user->rank_id = $newRank->id;
                $user->rank = $newRank->name;
                $user->last_rank_update = now();
                $user->saveQuietly();
                
                // Enregistrer l'historique
                \App\Models\RankHistory::create([
                    'user_id' => $user->id,
                    'old_rank_id' => $currentRank?->id ?? null,
                    'new_rank_id' => $newRank->id,
                    'old_rank_name' => $oldRank,
                    'new_rank_name' => $newRank->name,
                    'pv_at_time' => $user->pv_balance,
                    'bv_at_time' => $user->bv_balance,
                    'monthly_pv_at_time' => $user->monthly_pv,
                    'notes' => 'FixAllRanks command',
                ]);
                
                $updated++;
                $this->line("✅ {$user->name}: {$oldRank} → {$newRank->name}");
            }
        }
        
        // Afficher le tableau récapitulatif
        $this->newLine();
        $this->table(
            ['ID', 'Nom', 'Grade', 'PV', 'Team PV', 'CUMUL', 'Nv3', 'Nv4', 'Nv5', 'Nv6', 'Nouveau', 'MàJ'],
            array_map(function($r) {
                return [
                    $r['id'],
                    substr($r['name'], 0, 15),
                    $r['current_rank'],
                    $r['pv_personnel'],
                    $r['team_pv'],
                    $r['cumul_pv'],
                    $r['branches_niv3'],
                    $r['branches_niv4'],
                    $r['branches_niv5'],
                    $r['branches_niv6'],
                    $r['new_rank'],
                    $r['will_update']
                ];
            }, $results)
        );
        
        $this->newLine();
        $this->info("✅ {$updated} utilisateurs mis à jour !");
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

    private function countAllBranches(User $user): array
    {
        $result = ['niv3' => 0, 'niv4' => 0, 'niv5' => 0, 'niv6' => 0, 'total' => 0];
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();
        
        foreach ($children as $child) {
            $level = $child->rank_level ?? 1;
            
            if ($level >= 3) $result['niv3']++;
            if ($level >= 4) $result['niv4']++;
            if ($level >= 5) $result['niv5']++;
            if ($level >= 6) $result['niv6']++;
            $result['total']++;
        }
        
        return $result;
    }
}