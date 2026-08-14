<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\AdvancedRankCalculator;
use App\Services\MLM\RankUpdateService;
use Illuminate\Console\Command;

class FixAllRanks extends Command
{
    protected $signature = 'ranks:fix-all {--dry-run : Simulation sans modification}';
    protected $description = 'Corrige tous les grades avec le calcul CUMUL = PV Personnel + Team PV';

    public function handle(AdvancedRankCalculator $calculator, RankUpdateService $rankService)
    {
        $dryRun = $this->option('dry-run');
        
        $users = User::where('is_active', true)->get();
        $this->info("Traitement de " . $users->count() . " utilisateurs...");
        
        if ($dryRun) {
            $this->warn("Mode SIMULATION - Aucune modification ne sera faite");
        }
        
        $results = [];
        $updated = 0;
        
        foreach ($users as $user) {
            $teamPV = $this->calculateTeamPV($user);
            $cumulPV = ($user->pv_balance ?? 0) + $teamPV;
            $branches = $this->countAllBranches($user);
            
            if (!$dryRun) {
                $user->team_pv = $teamPV;
                $user->qualified_branches = $branches['total'];
                $user->saveQuietly();
            }
            
            $newRank = $calculator->calculateAdvancedRank($user);
            $currentRank = $user->rankObject;
            
            $currentRankId = 0;
            $currentRankName = 'Distributeur';
            $currentRankLevel = 1;
            
            if ($currentRank) {
                $currentRankId = $currentRank->id ?? 0;
                $currentRankName = $currentRank->name ?? 'Distributeur';
                $currentRankLevel = $currentRank->level ?? 1;
            }
            
            $newRankName = $newRank ? $newRank->name : 'Aucun';
            $newRankLevel = $newRank ? $newRank->level : 0;
            
            $willUpdate = ($newRank && $newRank->id != $currentRankId) ? 'YES' : 'NO';
            
            $results[] = [
                'id' => $user->id,
                'name' => $user->name,
                'current_rank' => $currentRankName,
                'current_level' => $currentRankLevel,
                'pv_personnel' => $user->pv_balance ?? 0,
                'team_pv' => $teamPV,
                'cumul_pv' => $cumulPV,
                'branches_niv3' => $branches['niv3'],
                'branches_niv4' => $branches['niv4'],
                'branches_niv5' => $branches['niv5'],
                'branches_niv6' => $branches['niv6'],
                'new_rank' => $newRankName,
                'new_level' => $newRankLevel,
                'will_update' => $willUpdate,
            ];
            
            if (!$dryRun && $newRank && $newRank->id != $currentRankId) {
                $rankService->triggerRankUpdate($user, 'fix_all_ranks');
                $updated++;
                $this->line($user->name . ": " . $currentRankName . " -> " . $newRankName);
            }
        }
        
        $this->newLine();
        $this->table(
            ['ID', 'Nom', 'Grade', 'PV', 'Team PV', 'CUMUL', 'Nv3', 'Nv4', 'Nv5', 'Nv6', 'Nouveau', 'M a J'],
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
        $this->info($updated . ' utilisateurs mis a jour');
    }

    private function calculateTeamPV(User $user): int
    {
        $total = $user->pv_balance ?? 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();
        
        foreach ($children as $child) {
            $total += $this->calculateTeamPV($child);
        }
        
        return (int) $total;
    }

    private function countAllBranches(User $user): array
    {
        $result = [
            'niv3' => 0,
            'niv4' => 0,
            'niv5' => 0,
            'niv6' => 0,
            'total' => 0
        ];
        
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();
        
        foreach ($children as $child) {
            $level = $child->rank_level ?? 1;
            
            if ($level >= 3) {
                $result['niv3']++;
            }
            if ($level >= 4) {
                $result['niv4']++;
            }
            if ($level >= 5) {
                $result['niv5']++;
            }
            if ($level >= 6) {
                $result['niv6']++;
            }
            $result['total']++;
        }
        
        return $result;
    }
}