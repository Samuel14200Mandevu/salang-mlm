<?php
// app/Services/MLM/RankConditionChecker.php

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
    protected array $branchLevelCache = [];

    public function checkConditions(User $user, Rank $rank): bool
    {
        $rankLevel = $rank->level;

        Log::info('Checking conditions for rank', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'target_rank_level' => $rankLevel,
            'target_rank_name' => $rank->name,
            'pv_balance' => $user->pv_balance,
            'team_pv' => $user->team_pv,
            'monthly_pv' => $user->monthly_pv,
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
    // NIVEAU 1 - DISTRIBUTEUR
    // ============================================================
    private function checkLevel1(User $user): bool
    {
        return true;
    }

    // ============================================================
    // NIVEAU 2 - QUALIFICATION
    // ============================================================
    private function checkLevel2(User $user): bool
    {
        return ($user->bv_balance ?? 0) >= 100;
    }

    // ============================================================
    // NIVEAU 3 - CUMUL DIRECTEUR
    // ============================================================
    private function checkLevel3(User $user): bool
    {
        return ($user->bv_balance ?? 0) >= 200;
    }

    // ============================================================
    // NIVEAU 4 - DIRECTEUR
    // ============================================================
    private function checkLevel4(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);

        if (($user->pv_balance ?? 0) >= 1000) {
            return true;
        }

        // ✅ Version optimisée avec CTE
        $branchesManager = $this->countQualifiedBranchesOptimized($user, 3);

        if ($branchesManager >= 3 && $cumulPV >= 1000) {
            return true;
        }

        if ($branchesManager >= 2 && $cumulPV >= 2200) {
            return true;
        }

        return false;
    }

    // ============================================================
    // NIVEAU 5 - MANAGER SENIOR
    // ============================================================
    private function checkLevel5(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);
        
        // ✅ Version optimisée avec CTE
        $branchesDirecteur = $this->countQualifiedBranchesOptimized($user, 4);
        $branchesManager = $this->countQualifiedBranchesOptimized($user, 3);

        if ($branchesDirecteur >= 3 && $cumulPV >= 3800) return true;
        if ($branchesDirecteur >= 2 && $cumulPV >= 7800) return true;
        if ($branchesDirecteur >= 2 && $branchesManager >= 4 && $cumulPV >= 3800) return true;
        if ($branchesDirecteur >= 1 && $branchesManager >= 6 && $cumulPV >= 3800) return true;

        return false;
    }

    // ============================================================
    // NIVEAU 6 - DIRECTEUR ENVOLÉE
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
    // NIVEAU 7 - SAPHIRE MANAGER
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
    // NIVEAU 8 - DIAMANT BLEU
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
    // NIVEAU 9 - DIAMOND PEARL
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
    // MÉTHODES UTILITAIRES OPTIMISÉES
    // ============================================================

    /**
     * Calcule le PV cumulé (personnel + équipe)
     */
    private function getCumulPV(User $user): float
    {
        return ($user->pv_balance ?? 0) + ($user->team_pv ?? 0);
    }

    /**
     * ✅ Compte les branches qualifiées avec CTE (1 requête)
     * Version OPTIMISÉE : une seule requête SQL pour tout le réseau
     */
    private function countQualifiedBranchesOptimized(User $user, int $rankLevel): int
    {
        $cacheKey = "branches_{$user->id}_rank_{$rankLevel}";
        
        if (isset($this->branchCache[$cacheKey])) {
            return $this->branchCache[$cacheKey];
        }

        // ✅ Version CORRIGÉE : chaque filleul direct = une branche distincte
        $result = DB::select("
            WITH RECURSIVE descendants AS (
                -- Point de départ : les filleuls directs (chaque branche distincte)
                SELECT 
                    id, 
                    parrain_id, 
                    rank_id, 
                    1 as depth,
                    id as branch_id,  -- Identifiant unique de la branche
                    CAST(id AS CHAR(1000)) as path
                FROM users 
                WHERE parrain_id = ?
                AND is_active = true
                
                UNION ALL
                
                -- Descendre récursivement dans chaque branche
                SELECT 
                    u.id, 
                    u.parrain_id, 
                    u.rank_id, 
                    d.depth + 1,
                    d.branch_id,  -- Garder l'identifiant de la branche racine
                    CONCAT(d.path, ',', u.id)
                FROM users u
                INNER JOIN descendants d ON u.parrain_id = d.id
                WHERE u.is_active = true
                AND FIND_IN_SET(u.id, d.path) = 0  -- Éviter les cycles
                AND d.depth < 50  -- Limite de sécurité
            ),
            branch_qualification AS (
                -- Pour chaque branche DISTINCTE, vérifier si elle a le niveau requis
                SELECT 
                    branch_id,
                    MAX(CASE 
                        WHEN COALESCE(r.level, 1) >= ? THEN 1 
                        ELSE 0 
                    END) as is_qualified
                FROM descendants d
                LEFT JOIN ranks r ON d.rank_id = r.id
                GROUP BY branch_id  -- GROUP BY sur la branche racine
            )
            SELECT COUNT(*) as qualified_count
            FROM branch_qualification
            WHERE is_qualified = 1
        ", [$user->id, $rankLevel]);

        $count = $result[0]->qualified_count ?? 0;
        $this->branchCache[$cacheKey] = $count;
        
        return $count;
    }
    /**
     * Récupère le niveau de grade d'un utilisateur
     */
    private function getUserRankLevel(User $user): int
    {
        if (isset($this->rankLevelCache[$user->id])) {
            return $this->rankLevelCache[$user->id];
        }

        if ($user->relationLoaded('rank') && $user->rank && !is_string($user->rank)) {
            $level = $user->rank->level ?? 1;
            $this->rankLevelCache[$user->id] = $level;
            return $level;
        }

        if ($user->rank_id) {
            $rank = Rank::find($user->rank_id);
            if ($rank) {
                $this->rankLevelCache[$user->id] = $rank->level;
                return $rank->level;
            }
        }

        if (is_string($user->rank)) {
            $rank = Rank::where('name', $user->rank)->first();
            if ($rank) {
                $this->rankLevelCache[$user->id] = $rank->level;
                return $rank->level;
            }
        }

        $this->rankLevelCache[$user->id] = 1;
        return 1;
    }

    /**
     * Vide le cache
     */
    public function clearCache(): void
    {
        $this->rankLevelCache = [];
        $this->branchCache = [];
        Cache::forget('rank_conditions_cache');
    }
}