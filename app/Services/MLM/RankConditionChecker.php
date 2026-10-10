<?php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Rank;
use App\Support\SqlDialect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RankConditionChecker
{
    protected array $rankLevelCache = [];
    protected array $branchCache = [];

    public function checkConditions(User $user, Rank $rank): bool
    {
        $rankLevel = (int) $rank->level;

        Log::info('Checking conditions for rank', [
            'user_id' => $user->id,
            'target_rank_level' => $rankLevel,
            'pv_balance' => $user->pv_balance,
            'team_pv' => $user->team_pv,
            'cumul_pv' => $this->getCumulPV($user),
        ]);

        switch ($rankLevel) {
            case 1: return $this->checkLevel1($user);
            case 2: return $this->checkLevel2($user);
            case 3: return $this->checkLevel3($user);
            case 4: return $this->checkLevel4($user);
            case 5: return $this->checkLevel5($user);
            case 6: return $this->checkLevel6($user);
            case 7: return $this->checkLevel7($user);
            case 8: return $this->checkLevel8($user);
            case 9: return $this->checkLevel9($user);
            default: return false;
        }
    }

    // ============================================================
    // ✅ HELPER : Normalise une valeur PV en float
    //    Gère : null, "", "715.0", "715,0", " 715 ", 715
    // ============================================================
    private function normalizePV($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $str = str_replace(',', '.', trim((string) $value));
        $str = preg_replace('/\s+/', '', $str);

        return is_numeric($str) ? (float) $str : 0.0;
    }

    // ============================================================
    // NIVEAU 1 — DISTRIBUTEUR
    // ============================================================
    private function checkLevel1(User $user): bool
    {
        return true;
    }

    // ============================================================
    // NIVEAU 2 — QUALIFICATION : 100 PV PERSONNELS
    // ============================================================
    private function checkLevel2(User $user): bool
    {
        $pv = $this->normalizePV($user->pv_balance);
        return $pv >= 100;
    }

    // ============================================================
    // NIVEAU 3 — CUMUL DIRECTEUR : 200 PV PERSONNELS
    // ============================================================
    private function checkLevel3(User $user): bool
    {
        $pv = $this->normalizePV($user->pv_balance);
        return $pv >= 200;
    }

    // ============================================================
    // NIVEAU 4 — DIRECTEUR
    // ============================================================
    private function checkLevel4(User $user): bool
    {
        if ($user->rank4_grandfathered) {
            return true;
        }

        $rules = config('ranks.level_4_qualification', []);
        $branchMinRank = (int) ($rules['branch_min_rank_level'] ?? 3);
        $cumulPV = $this->getCumulPV($user);
        $qualifiedBranches = $this->countQualifiedBranchesOptimized($user, $branchMinRank);
        $directReferrals = $this->countDirectActiveReferrals($user);

        $simpleBranches = (int) ($rules['branches_simple_count'] ?? 3);
        $simpleCumul = (float) ($rules['team_cumul_simple'] ?? 1000);
        if ($qualifiedBranches >= $simpleBranches && $cumulPV >= $simpleCumul) {
            return true;
        }

        $doubleMinBranches = (int) ($rules['branches_double_min'] ?? 1);
        $doubleMaxBranches = (int) ($rules['branches_double_max'] ?? 2);
        $doubleCumul = (float) ($rules['team_cumul_double'] ?? 2200);
        if ($directReferrals >= 1
            && $qualifiedBranches >= $doubleMinBranches
            && $qualifiedBranches <= $doubleMaxBranches
            && $cumulPV >= $doubleCumul
        ) {
            return true;
        }

        $soloMonthlyPv = (float) ($rules['personal_monthly_pv_solo'] ?? 1000);
        $monthlyPv = $this->normalizePV($user->monthly_pv);
        if ($directReferrals === 0 && $monthlyPv >= $soloMonthlyPv) {
            return true;
        }

        return false;
    }

    private function countDirectActiveReferrals(User $user): int
    {
        return User::query()
            ->where('parrain_id', $user->id)
            ->where('is_active', true)
            ->count();
    }

    // ============================================================
    // NIVEAU 5 — MANAGER SENIOR
    // ============================================================
    private function checkLevel5(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);

        $branchesDirecteur = $this->countQualifiedBranchesOptimized($user, 4);

        if ($branchesDirecteur >= 3 && $cumulPV >= 3800) return true;
        if ($branchesDirecteur >= 2 && $cumulPV >= 7800) return true;
        if ($this->satisfiesExclusiveMixedBranches($user, [2 => 4, 4 => 3]) && $cumulPV >= 3800) return true;
        if ($this->satisfiesExclusiveMixedBranches($user, [1 => 4, 6 => 3]) && $cumulPV >= 3800) return true;

        return false;
    }

    // ============================================================
    // NIVEAU 6 — DIRECTEUR ENVOLÉE
    // ============================================================
    private function checkLevel6(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);

        $branchesManagerSenior = $this->countQualifiedBranchesOptimized($user, 5);

        if ($branchesManagerSenior >= 3 && $cumulPV >= 16000) return true;
        if ($branchesManagerSenior >= 2 && $cumulPV >= 35000) return true;
        if ($this->satisfiesExclusiveMixedBranches($user, [2 => 5, 4 => 4]) && $cumulPV >= 16000) return true;
        if ($this->satisfiesExclusiveMixedBranches($user, [1 => 5, 6 => 4]) && $cumulPV >= 16000) return true;

        return false;
    }

    // ============================================================
    // NIVEAU 7 — SAPHIRE MANAGER
    // ============================================================
    private function checkLevel7(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);

        $branchesDirecteurEnvolee = $this->countQualifiedBranchesOptimized($user, 6);

        if ($branchesDirecteurEnvolee >= 3 && $cumulPV >= 73000) return true;
        if ($branchesDirecteurEnvolee >= 2 && $cumulPV >= 145000) return true;
        if ($this->satisfiesExclusiveMixedBranches($user, [2 => 6, 4 => 5]) && $cumulPV >= 73000) return true;
        if ($this->satisfiesExclusiveMixedBranches($user, [1 => 6, 6 => 5]) && $cumulPV >= 73000) return true;

        return false;
    }

    // ============================================================
    // NIVEAU 8 — DIAMANT BLEU
    // ============================================================
    private function checkLevel8(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);

        $branchesSaphire = $this->countQualifiedBranchesOptimized($user, 7);

        if ($branchesSaphire >= 3 && $cumulPV >= 280000) return true;
        if ($branchesSaphire >= 2 && $cumulPV >= 580000) return true;
        if ($this->satisfiesExclusiveMixedBranches($user, [2 => 7, 4 => 6]) && $cumulPV >= 280000) return true;
        if ($this->satisfiesExclusiveMixedBranches($user, [1 => 7, 6 => 6]) && $cumulPV >= 280000) return true;

        return false;
    }

    // ============================================================
    // NIVEAU 9 — PERLE DIAMANT
    // ============================================================
    private function checkLevel9(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);

        $branchesDiamondBlue = $this->countQualifiedBranchesOptimized($user, 8);

        if ($branchesDiamondBlue >= 3 && $cumulPV >= 400000) return true;
        if ($branchesDiamondBlue >= 2 && $cumulPV >= 780000) return true;
        if ($this->satisfiesExclusiveMixedBranches($user, [2 => 8, 4 => 7]) && $cumulPV >= 400000) return true;
        if ($this->satisfiesExclusiveMixedBranches($user, [1 => 8, 6 => 7]) && $cumulPV >= 400000) return true;

        return false;
    }

    // ============================================================
    // MÉTHODES UTILITAIRES
    // ============================================================

    private function getCumulPV(User $user): float
    {
        return (float) ($user->team_pv ?? 0);
    }

    /**
     * Options mixtes : chaque filleul direct = une branche ; le niveau retenu est le MAX
     * dans toute la descendance. Les branches comptées pour un palier ne sont pas réutilisées
     * pour un palier inférieur (ex. 2× niv.5 + 4× niv.4 = 6 jambes distinctes).
     *
     * @param  array<int, int>  $branchCountsByMinRank  ex. [2 => 5, 4 => 4] → 2 br. niv.≥5 et 4 autres br. niv.≥4
     */
    public function satisfiesExclusiveMixedBranches(User $user, array $branchCountsByMinRank): bool
    {
        if ($branchCountsByMinRank === []) {
            return false;
        }

        $requirements = [];
        foreach ($branchCountsByMinRank as $count => $minRankLevel) {
            $count = (int) $count;
            $minRankLevel = (int) $minRankLevel;
            if ($count <= 0 || $minRankLevel <= 0) {
                return false;
            }
            $requirements[] = ['count' => $count, 'min_rank' => $minRankLevel];
        }

        usort($requirements, fn (array $a, array $b): int => $b['min_rank'] <=> $a['min_rank']);

        $branchMaxRanks = $this->getBranchMaxRankLevels($user);
        if ($branchMaxRanks === []) {
            return false;
        }

        $usedBranchIds = [];

        foreach ($requirements as $requirement) {
            $candidates = [];
            foreach ($branchMaxRanks as $branchId => $maxRank) {
                if (in_array($branchId, $usedBranchIds, true)) {
                    continue;
                }
                if ($maxRank >= $requirement['min_rank']) {
                    $candidates[$branchId] = $maxRank;
                }
            }

            if (count($candidates) < $requirement['count']) {
                return false;
            }

            arsort($candidates);
            $selected = array_slice(array_keys($candidates), 0, $requirement['count']);
            foreach ($selected as $branchId) {
                $usedBranchIds[] = $branchId;
            }
        }

        return true;
    }

    /**
     * @return array<int, int> branch_id (filleul direct) => meilleur niveau de grade dans la branche
     */
    private function getBranchMaxRankLevels(User $user): array
    {
        $cacheKey = "branch_max_levels_{$user->id}";

        if (isset($this->branchCache[$cacheKey])) {
            return $this->branchCache[$cacheKey];
        }

        $appendPath = SqlDialect::appendToPath('d.path', 'u.id');
        $notInPath = SqlDialect::notInPath('u.id', 'd.path');
        $pathSeed = SqlDialect::branchPathSeed('id');
        $activeRoot = SqlDialect::userIsActive();
        $activeChild = SqlDialect::userIsActive('u');

        $rows = DB::select("
            WITH RECURSIVE descendants AS (
                SELECT 
                    id, 
                    parrain_id, 
                    rank_id,
                    rank_level,
                    1 as depth,
                    id as branch_id,
                    {$pathSeed} as path
                FROM users 
                WHERE parrain_id = ?
                AND {$activeRoot}
                
                UNION ALL
                
                SELECT 
                    u.id, 
                    u.parrain_id, 
                    u.rank_id,
                    u.rank_level,
                    d.depth + 1,
                    d.branch_id,
                    {$appendPath} as path
                FROM users u
                INNER JOIN descendants d ON u.parrain_id = d.id
                WHERE {$activeChild}
                AND {$notInPath}
                AND d.depth < 50
            )
            SELECT 
                branch_id,
                MAX(COALESCE(r.level, d.rank_level, 1)) as max_level
            FROM descendants d
            LEFT JOIN ranks r ON d.rank_id = r.id
            GROUP BY branch_id
        ", [$user->id]);

        $levels = [];
        foreach ($rows as $row) {
            $levels[(int) $row->branch_id] = (int) $row->max_level;
        }

        $this->branchCache[$cacheKey] = $levels;

        return $levels;
    }

    public function countQualifiedBranches(User $user, int $rankLevel): int
    {
        return $this->countQualifiedBranchesOptimized($user, $rankLevel);
    }

    /**
     * @return array<int, int> filleul direct (id) => meilleur niveau de grade dans la branche
     */
    public function getDirectBranchMaxRankLevels(User $user): array
    {
        return $this->getBranchMaxRankLevels($user);
    }

    private function countQualifiedBranchesOptimized(User $user, int $rankLevel): int
    {
        $cacheKey = "branches_{$user->id}_rank_{$rankLevel}";

        if (isset($this->branchCache[$cacheKey])) {
            return $this->branchCache[$cacheKey];
        }

        $appendPath = SqlDialect::appendToPath('d.path', 'u.id');
        $notInPath = SqlDialect::notInPath('u.id', 'd.path');
        $pathSeed = SqlDialect::branchPathSeed('id');
        $activeRoot = SqlDialect::userIsActive();
        $activeChild = SqlDialect::userIsActive('u');

        $result = DB::select("
            WITH RECURSIVE descendants AS (
                SELECT 
                    id, 
                    parrain_id, 
                    rank_id,
                    rank_level,
                    1 as depth,
                    id as branch_id,
                    {$pathSeed} as path
                FROM users 
                WHERE parrain_id = ?
                AND {$activeRoot}
                
                UNION ALL
                
                SELECT 
                    u.id, 
                    u.parrain_id, 
                    u.rank_id,
                    u.rank_level,
                    d.depth + 1,
                    d.branch_id,
                    {$appendPath} as path
                FROM users u
                INNER JOIN descendants d ON u.parrain_id = d.id
                WHERE {$activeChild}
                AND {$notInPath}
                AND d.depth < 50
            ),
            branch_qualification AS (
                SELECT 
                    branch_id,
                    MAX(CASE 
                        WHEN COALESCE(r.level, d.rank_level, 1) >= ? THEN 1 
                        ELSE 0 
                    END) as is_qualified
                FROM descendants d
                LEFT JOIN ranks r ON d.rank_id = r.id
                GROUP BY branch_id
            )
            SELECT COUNT(*) as qualified_count
            FROM branch_qualification
            WHERE is_qualified = 1
        ", [$user->id, $rankLevel]);

        $count = (int) ($result[0]->qualified_count ?? 0);
        $this->branchCache[$cacheKey] = $count;

        return $count;
    }

    public function clearCache(): void
    {
        $this->rankLevelCache = [];
        $this->branchCache = [];
        Cache::forget('rank_conditions_cache');
    }
}