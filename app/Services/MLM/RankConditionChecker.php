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
    /**
     * Vérifier si un utilisateur remplit les conditions pour un niveau
     */
    public function checkConditions(User $user, Rank $rank): bool
    {
        $rankLevel = $rank->level;

        // 🔥 DEBUG: Log pour voir les données
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

    /**
     * Niveau 1 : Distributeur
     */
    private function checkLevel1(User $user): bool
    {
        return true;
    }

    /**
     * Niveau 2 : Qualification
     * PV personnel ≥ 100
     */
    private function checkLevel2(User $user): bool
    {
        return ($user->pv_balance ?? 0) >= 100;
    }

    /**
     * Niveau 3 : Cumul Directeur
     * PV personnel ≥ 200
     */
    private function checkLevel3(User $user): bool
    {
        return ($user->pv_balance ?? 0) >= 200;
    }

    /**
     * Niveau 4 : Directeur
     */
    private function checkLevel4(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);

        // Option 1: PV ≥ 1000
        if (($user->pv_balance ?? 0) >= 1000) {
            Log::info('Level 4 - Option 1 passed', ['user_id' => $user->id]);
            return true;
        }

        $branchesNiveau3 = $this->countQualifiedBranches($user, 3);

        // Option 2: 3 branches Niveau 3 + CUMUL ≥ 1000
        if ($branchesNiveau3 >= 3 && $cumulPV >= 1000) {
            Log::info('Level 4 - Option 2 passed', ['user_id' => $user->id, 'branches' => $branchesNiveau3]);
            return true;
        }

        // Option 3: 2 branches Niveau 3 + CUMUL ≥ 2200
        if ($branchesNiveau3 >= 2 && $cumulPV >= 2200) {
            Log::info('Level 4 - Option 3 passed', ['user_id' => $user->id, 'branches' => $branchesNiveau3]);
            return true;
        }

        return false;
    }

    /**
     * Niveau 5 : Manager Senior
     */
    private function checkLevel5(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);
        $branchesNiveau4 = $this->countQualifiedBranches($user, 4);
        $branchesNiveau3 = $this->countQualifiedBranches($user, 3);

        Log::info('Level 5 check', [
            'user_id' => $user->id,
            'cumul' => $cumulPV,
            'branches_n4' => $branchesNiveau4,
            'branches_n3' => $branchesNiveau3,
        ]);

        if ($branchesNiveau4 >= 3 && $cumulPV >= 3800) {
            Log::info('Level 5 - Option 1 passed', ['user_id' => $user->id]);
            return true;
        }

        if ($branchesNiveau4 >= 2 && $cumulPV >= 7800) {
            Log::info('Level 5 - Option 2 passed', ['user_id' => $user->id]);
            return true;
        }

        if ($branchesNiveau4 >= 2 && $branchesNiveau3 >= 4 && $cumulPV >= 3800) {
            Log::info('Level 5 - Option 3 passed', ['user_id' => $user->id]);
            return true;
        }

        if ($branchesNiveau4 >= 1 && $branchesNiveau3 >= 6 && $cumulPV >= 3800) {
            Log::info('Level 5 - Option 4 passed', ['user_id' => $user->id]);
            return true;
        }

        return false;
    }

    /**
     * Niveau 6 : Directeur Envolée
     */
    private function checkLevel6(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);
        $branchesNiveau5 = $this->countQualifiedBranches($user, 5);
        $branchesNiveau4 = $this->countQualifiedBranches($user, 4);

        if ($branchesNiveau5 >= 3 && $cumulPV >= 16000) return true;
        if ($branchesNiveau5 >= 2 && $cumulPV >= 35000) return true;
        if ($branchesNiveau5 >= 2 && $branchesNiveau4 >= 4 && $cumulPV >= 16000) return true;
        if ($branchesNiveau5 >= 1 && $branchesNiveau4 >= 6 && $cumulPV >= 16000) return true;

        return false;
    }

    /**
     * Niveau 7 : Saphire Manager
     */
    private function checkLevel7(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);
        $branchesNiveau6 = $this->countQualifiedBranches($user, 6);
        $branchesNiveau5 = $this->countQualifiedBranches($user, 5);

        if ($branchesNiveau6 >= 3 && $cumulPV >= 73000) return true;
        if ($branchesNiveau6 >= 2 && $cumulPV >= 145000) return true;
        if ($branchesNiveau6 >= 2 && $branchesNiveau5 >= 4 && $cumulPV >= 73000) return true;
        if ($branchesNiveau6 >= 1 && $branchesNiveau5 >= 6 && $cumulPV >= 73000) return true;

        return false;
    }

    /**
     * Niveau 8 : Diamant Bleu
     */
    private function checkLevel8(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);
        $branchesNiveau7 = $this->countQualifiedBranches($user, 7);
        $branchesNiveau6 = $this->countQualifiedBranches($user, 6);

        if ($branchesNiveau7 >= 3 && $cumulPV >= 280000) return true;
        if ($branchesNiveau7 >= 2 && $cumulPV >= 580000) return true;
        if ($branchesNiveau7 >= 2 && $branchesNiveau6 >= 4 && $cumulPV >= 280000) return true;
        if ($branchesNiveau7 >= 1 && $branchesNiveau6 >= 6 && $cumulPV >= 280000) return true;

        return false;
    }

    /**
     * Niveau 9 : Diamond Pearl
     */
    private function checkLevel9(User $user): bool
    {
        $cumulPV = $this->getCumulPV($user);
        $branchesNiveau8 = $this->countQualifiedBranches($user, 8);
        $branchesNiveau7 = $this->countQualifiedBranches($user, 7);

        if ($branchesNiveau8 >= 3 && $cumulPV >= 400000) return true;
        if ($branchesNiveau8 >= 2 && $cumulPV >= 780000) return true;
        if ($branchesNiveau8 >= 2 && $branchesNiveau7 >= 4 && $cumulPV >= 400000) return true;
        if ($branchesNiveau8 >= 1 && $branchesNiveau7 >= 6 && $cumulPV >= 400000) return true;

        return false;
    }

    /**
     * CALCUL DU CUMUL = PV Personnel + Team PV
     */
    private function getCumulPV(User $user): int
    {
        return ($user->pv_balance ?? 0) + ($user->team_pv ?? 0);
    }

    /**
     * Compter les branches qualifiées pour un niveau donné
     * Une branche est qualifiée si le filleul a le niveau requis
     */
    private function countQualifiedBranches(User $user, int $rankLevel): int
    {
        $count = 0;
        
        // ✅ Charger les filleuls avec leurs relations
        $filleuls = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->with('rank')
            ->get();

        foreach ($filleuls as $filleul) {
            // Vérifier si le filleul a le niveau requis (lui ou ses descendants)
            if ($this->hasRankLevel($filleul, $rankLevel)) {
                $count++;
            }
        }

        Log::info('Count qualified branches', [
            'user_id' => $user->id,
            'rank_level' => $rankLevel,
            'count' => $count,
            'filleuls_count' => $filleuls->count(),
        ]);

        return $count;
    }

    /**
     * Vérifier si un utilisateur ou ses descendants ont un niveau donné
     */
    private function hasRankLevel(User $user, int $rankLevel, int $maxDepth = 20, int $currentDepth = 0): bool
    {
        if ($currentDepth >= $maxDepth) {
            return false;
        }

        // Récupérer le niveau de l'utilisateur
        $userLevel = $this->getUserRankLevel($user);

        // L'utilisateur lui-même a le niveau requis
        if ($userLevel >= $rankLevel) {
            return true;
        }

        // Vérifier récursivement les descendants
        $descendants = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->with('rank')
            ->get();

        foreach ($descendants as $descendant) {
            if ($this->hasRankLevel($descendant, $rankLevel, $maxDepth, $currentDepth + 1)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir le niveau d'un utilisateur
     */
    private function getUserRankLevel(User $user): int
    {
        // Si le user a une relation rank chargée
        if ($user->relationLoaded('rank') && $user->rank && !is_string($user->rank)) {
            return $user->rank->level ?? 1;
        }

        // Si le user a un rank_id
        if ($user->rank_id) {
            $rank = Rank::find($user->rank_id);
            if ($rank) {
                return $rank->level;
            }
        }

        // Si le user a un champ rank (string)
        if (is_string($user->rank)) {
            $rank = Rank::where('name', $user->rank)->first();
            if ($rank) {
                return $rank->level;
            }
        }

        return 1;
    }

    /**
     * Vider le cache interne si nécessaire
     */
    public function clearCache(): void
    {
        Cache::forget('rank_conditions_cache');
    }
}