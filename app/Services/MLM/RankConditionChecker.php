<?php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Rank;
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
        $pvPerso = $this->normalizePV($user->pv_balance);

        if ($pvPerso >= 1000) {
            return true;
        }

        $cumulPV = $this->getCumulPV($user);
        $branchesManager = $this->countQualifiedBranchesOptimized($user, 3);

        if ($branchesManager >= 3 && $cumulPV >= 1000) return true;
        if ($branchesManager >= 2 && $cumulPV >= 2200) return true;

        return false;
    }

    // ============================================================
    // NIVEAU 5 — MANAGER SENIOR
    // ============================================================
    private function checkLevel5(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);

        $branchesDirecteur = $this->countQualifiedBranchesOptimized($user, 4);
        $branchesManager = $this->countQualifiedBranchesOptimized($user, 3);

        if ($branchesDirecteur >= 3 && $cumulPV >= 3800) return true;
        if ($branchesDirecteur >= 2 && $cumulPV >= 7800) return true;
        if ($branchesDirecteur >= 2 && $branchesManager >= 4 && $cumulPV >= 3800) return true;
        if ($branchesDirecteur >= 1 && $branchesManager >= 6 && $cumulPV >= 3800) return true;

        return false;
    }

    // ============================================================
    // NIVEAU 6 — DIRECTEUR ENVOLÉE
    // ============================================================
    private function checkLevel6(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);

        $branchesManagerSenior = $this->countQualifiedBranchesOptimized($user, 5);
        $branchesDirecteur = $this->countQualifiedBranchesOptimized($user, 4);

        if ($branchesManagerSenior >= 3 && $cumulPV >= 16000) return true;
        if ($branchesManagerSenior >= 2 && $cumulPV >= 35000) return true;
        if ($branchesManagerSenior >= 2 && $branchesDirecteur >= 4 && $cumulPV >= 16000) return true;
        if ($branchesManagerSenior >= 1 && $branchesDirecteur >= 6 && $cumulPV >= 16000) return true;

        return false;
    }

    // ============================================================
    // NIVEAU 7 — SAPHIRE MANAGER
    // ============================================================
    private function checkLevel7(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);

        $branchesDirecteurEnvolee = $this->countQualifiedBranchesOptimized($user, 6);
        $branchesManagerSenior = $this->countQualifiedBranchesOptimized($user, 5);

        if ($branchesDirecteurEnvolee >= 3 && $cumulPV >= 73000) return true;
        if ($branchesDirecteurEnvolee >= 2 && $cumulPV >= 145000) return true;
        if ($branchesDirecteurEnvolee >= 2 && $branchesManagerSenior >= 4 && $cumulPV >= 73000) return true;
        if ($branchesDirecteurEnvolee >= 1 && $branchesManagerSenior >= 6 && $cumulPV >= 73000) return true;

        return false;
    }

    // ============================================================
    // NIVEAU 8 — DIAMANT BLEU
    // ============================================================
    private function checkLevel8(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);

        $branchesSaphire = $this->countQualifiedBranchesOptimized($user, 7);
        $branchesDirecteurEnvolee = $this->countQualifiedBranchesOptimized($user, 6);

        if ($branchesSaphire >= 3 && $cumulPV >= 280000) return true;
        if ($branchesSaphire >= 2 && $cumulPV >= 580000) return true;
        if ($branchesSaphire >= 2 && $branchesDirecteurEnvolee >= 4 && $cumulPV >= 280000) return true;
        if ($branchesSaphire >= 1 && $branchesDirecteurEnvolee >= 6 && $cumulPV >= 280000) return true;

        return false;
    }

    // ============================================================
    // NIVEAU 9 — PERLE DIAMANT
    // ============================================================
    private function checkLevel9(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);

        $branchesDiamondBlue = $this->countQualifiedBranchesOptimized($user, 8);
        $branchesSaphire = $this->countQualifiedBranchesOptimized($user, 7);

        if ($branchesDiamondBlue >= 3 && $cumulPV >= 400000) return true;
        if ($branchesDiamondBlue >= 2 && $cumulPV >= 780000) return true;
        if ($branchesDiamondBlue >= 2 && $branchesSaphire >= 4 && $cumulPV >= 400000) return true;
        if ($branchesDiamondBlue >= 1 && $branchesSaphire >= 6 && $cumulPV >= 400000) return true;

        return false;
    }

    // ============================================================
    // MÉTHODES UTILITAIRES
    // ============================================================

    private function getCumulPV(User $user): float
    {
        return (float) ($user->team_pv ?? 0);
    }

    private function countQualifiedBranchesOptimized(User $user, int $rankLevel): int
    {
        $cacheKey = "branches_{$user->id}_rank_{$rankLevel}";

        if (isset($this->branchCache[$cacheKey])) {
            return $this->branchCache[$cacheKey];
        }

        $result = DB::select("
            WITH RECURSIVE descendants AS (
                SELECT 
                    id, 
                    parrain_id, 
                    rank_id, 
                    1 as depth,
                    id as branch_id,
                    CAST(id AS CHAR(1000)) as path
                FROM users 
                WHERE parrain_id = ?
                AND is_active = true
                
                UNION ALL
                
                SELECT 
                    u.id, 
                    u.parrain_id, 
                    u.rank_id, 
                    d.depth + 1,
                    d.branch_id,
                    CONCAT(d.path, ',', u.id)
                FROM users u
                INNER JOIN descendants d ON u.parrain_id = d.id
                WHERE u.is_active = true
                AND FIND_IN_SET(u.id, d.path) = 0
                AND d.depth < 50
            ),
            branch_qualification AS (
                SELECT 
                    branch_id,
                    MAX(CASE 
                        WHEN COALESCE(r.level, 1) >= ? THEN 1 
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