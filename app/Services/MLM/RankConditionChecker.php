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
        return $user->pv_balance >= 100;
    }

    /**
     * Niveau 3 : Cumul Directeur
     * PV personnel ≥ 200
     */
    private function checkLevel3(User $user): bool
    {
        return $user->pv_balance >= 200;
    }

    /**
     * Niveau 4 : Directeur
     * 
     * Option 1: PV ≥ 1000
     * Option 2: 3 branches Niveau 3 + CUMUL ≥ 1000 PV
     * Option 3: 2 branches Niveau 3 + CUMUL ≥ 2200 PV
     * 
     * CUMUL = PV Personnel + Team PV
     */
    private function checkLevel4(User $user): bool
    {
        // Calcul du CUMUL
        $cumulPV = $this->getCumulPV($user);

        // Option 1: PV ≥ 1000
        if ($user->pv_balance >= 1000) {
            return true;
        }

        $branchesNiveau3 = $this->countQualifiedBranches($user, 3);

        // Option 2: 3 branches Niveau 3 + CUMUL ≥ 1000
        if ($branchesNiveau3 >= 3 && $cumulPV >= 1000) {
            return true;
        }

        // Option 3: 2 branches Niveau 3 + CUMUL ≥ 2200
        if ($branchesNiveau3 >= 2 && $cumulPV >= 2200) {
            return true;
        }

        return false;
    }

    /**
     * Niveau 5 : Manager Senior
     * 
     * Option 1: 3 branches Niveau 4 + CUMUL ≥ 3800 PV
     * Option 2: 2 branches Niveau 4 + CUMUL ≥ 7800 PV
     * Option 3: 2 branches Niveau 4 + 4 branches Niveau 3 + CUMUL ≥ 3800 PV
     * Option 4: 1 branche Niveau 4 + 6 branches Niveau 3 + CUMUL ≥ 3800 PV
     * 
     * CUMUL = PV Personnel + Team PV
     */
    private function checkLevel5(User $user): bool
    {
        // Calcul du CUMUL
        $cumulPV = $this->getCumulPV($user);

        $branchesNiveau4 = $this->countQualifiedBranches($user, 4);
        $branchesNiveau3 = $this->countQualifiedBranches($user, 3);

        // Option 1: 3 branches Niveau 4 + CUMUL ≥ 3800
        if ($branchesNiveau4 >= 3 && $cumulPV >= 3800) {
            return true;
        }

        // Option 2: 2 branches Niveau 4 + CUMUL ≥ 7800
        if ($branchesNiveau4 >= 2 && $cumulPV >= 7800) {
            return true;
        }

        // Option 3: 2 branches Niveau 4 + 4 branches Niveau 3 + CUMUL ≥ 3800
        if ($branchesNiveau4 >= 2 && $branchesNiveau3 >= 4 && $cumulPV >= 3800) {
            return true;
        }

        // Option 4: 1 branche Niveau 4 + 6 branches Niveau 3 + CUMUL ≥ 3800
        if ($branchesNiveau4 >= 1 && $branchesNiveau3 >= 6 && $cumulPV >= 3800) {
            return true;
        }

        return false;
    }

    /**
     * Niveau 6 : Directeur Envolée
     * 
     * Option 1: 3 branches Niveau 5 + CUMUL ≥ 16000 PV
     * Option 2: 2 branches Niveau 5 + CUMUL ≥ 35000 PV
     * Option 3: 2 branches Niveau 5 + 4 branches Niveau 4 + CUMUL ≥ 16000 PV
     * Option 4: 1 branche Niveau 5 + 6 branches Niveau 4 + CUMUL ≥ 16000 PV
     * 
     * CUMUL = PV Personnel + Team PV
     */
    private function checkLevel6(User $user): bool
    {
        // Calcul du CUMUL
        $cumulPV = $this->getCumulPV($user);

        $branchesNiveau5 = $this->countQualifiedBranches($user, 5);
        $branchesNiveau4 = $this->countQualifiedBranches($user, 4);

        // Option 1: 3 branches Niveau 5 + CUMUL ≥ 16000
        if ($branchesNiveau5 >= 3 && $cumulPV >= 16000) {
            return true;
        }

        // Option 2: 2 branches Niveau 5 + CUMUL ≥ 35000
        if ($branchesNiveau5 >= 2 && $cumulPV >= 35000) {
            return true;
        }

        // Option 3: 2 branches Niveau 5 + 4 branches Niveau 4 + CUMUL ≥ 16000
        if ($branchesNiveau5 >= 2 && $branchesNiveau4 >= 4 && $cumulPV >= 16000) {
            return true;
        }

        // Option 4: 1 branche Niveau 5 + 6 branches Niveau 4 + CUMUL ≥ 16000
        if ($branchesNiveau5 >= 1 && $branchesNiveau4 >= 6 && $cumulPV >= 16000) {
            return true;
        }

        return false;
    }

    /**
     * Niveau 7 : Saphire Manager
     * 
     * Option 1: 3 branches Niveau 6 + CUMUL ≥ 73000 PV
     * Option 2: 2 branches Niveau 6 + CUMUL ≥ 145000 PV
     * Option 3: 2 branches Niveau 6 + 4 branches Niveau 5 + CUMUL ≥ 73000 PV
     * Option 4: 1 branche Niveau 6 + 6 branches Niveau 5 + CUMUL ≥ 73000 PV
     * 
     * CUMUL = PV Personnel + Team PV
     */
    private function checkLevel7(User $user): bool
    {
        // Calcul du CUMUL
        $cumulPV = $this->getCumulPV($user);

        $branchesNiveau6 = $this->countQualifiedBranches($user, 6);
        $branchesNiveau5 = $this->countQualifiedBranches($user, 5);

        // Option 1: 3 branches Niveau 6 + CUMUL ≥ 73000
        if ($branchesNiveau6 >= 3 && $cumulPV >= 73000) {
            return true;
        }

        // Option 2: 2 branches Niveau 6 + CUMUL ≥ 145000
        if ($branchesNiveau6 >= 2 && $cumulPV >= 145000) {
            return true;
        }

        // Option 3: 2 branches Niveau 6 + 4 branches Niveau 5 + CUMUL ≥ 73000
        if ($branchesNiveau6 >= 2 && $branchesNiveau5 >= 4 && $cumulPV >= 73000) {
            return true;
        }

        // Option 4: 1 branche Niveau 6 + 6 branches Niveau 5 + CUMUL ≥ 73000
        if ($branchesNiveau6 >= 1 && $branchesNiveau5 >= 6 && $cumulPV >= 73000) {
            return true;
        }

        return false;
    }

    /**
     * Niveau 8 : Diamant Bleu
     * 
     * Option 1: 3 branches Niveau 7 + CUMUL ≥ 280000 PV
     * Option 2: 2 branches Niveau 7 + CUMUL ≥ 580000 PV
     * Option 3: 2 branches Niveau 7 + 4 branches Niveau 6 + CUMUL ≥ 280000 PV
     * Option 4: 1 branche Niveau 7 + 6 branches Niveau 6 + CUMUL ≥ 280000 PV
     * 
     * CUMUL = PV Personnel + Team PV
     */
    private function checkLevel8(User $user): bool
    {
        // Calcul du CUMUL
        $cumulPV = $this->getCumulPV($user);

        $branchesNiveau7 = $this->countQualifiedBranches($user, 7);
        $branchesNiveau6 = $this->countQualifiedBranches($user, 6);

        // Option 1: 3 branches Niveau 7 + CUMUL ≥ 280000
        if ($branchesNiveau7 >= 3 && $cumulPV >= 280000) {
            return true;
        }

        // Option 2: 2 branches Niveau 7 + CUMUL ≥ 580000
        if ($branchesNiveau7 >= 2 && $cumulPV >= 580000) {
            return true;
        }

        // Option 3: 2 branches Niveau 7 + 4 branches Niveau 6 + CUMUL ≥ 280000
        if ($branchesNiveau7 >= 2 && $branchesNiveau6 >= 4 && $cumulPV >= 280000) {
            return true;
        }

        // Option 4: 1 branche Niveau 7 + 6 branches Niveau 6 + CUMUL ≥ 280000
        if ($branchesNiveau7 >= 1 && $branchesNiveau6 >= 6 && $cumulPV >= 280000) {
            return true;
        }

        return false;
    }

    /**
     * Niveau 9 : Diamond Pearl
     * 
     * Option 1: 3 branches Niveau 8 + CUMUL ≥ 400000 PV
     * Option 2: 2 branches Niveau 8 + CUMUL ≥ 780000 PV
     * Option 3: 2 branches Niveau 8 + 4 branches Niveau 7 + CUMUL ≥ 400000 PV
     * Option 4: 1 branche Niveau 8 + 6 branches Niveau 7 + CUMUL ≥ 400000 PV
     * 
     * CUMUL = PV Personnel + Team PV
     */
    private function checkLevel9(User $user): bool
    {
        // Calcul du CUMUL
        $cumulPV = $this->getCumulPV($user);

        $branchesNiveau8 = $this->countQualifiedBranches($user, 8);
        $branchesNiveau7 = $this->countQualifiedBranches($user, 7);

        // Option 1: 3 branches Niveau 8 + CUMUL ≥ 400000
        if ($branchesNiveau8 >= 3 && $cumulPV >= 400000) {
            return true;
        }

        // Option 2: 2 branches Niveau 8 + CUMUL ≥ 780000
        if ($branchesNiveau8 >= 2 && $cumulPV >= 780000) {
            return true;
        }

        // Option 3: 2 branches Niveau 8 + 4 branches Niveau 7 + CUMUL ≥ 400000
        if ($branchesNiveau8 >= 2 && $branchesNiveau7 >= 4 && $cumulPV >= 400000) {
            return true;
        }

        // Option 4: 1 branche Niveau 8 + 6 branches Niveau 7 + CUMUL ≥ 400000
        if ($branchesNiveau8 >= 1 && $branchesNiveau7 >= 6 && $cumulPV >= 400000) {
            return true;
        }

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
     * Une branche est qualifiée si le filleul OU un de ses descendants a le niveau requis
     */
    private function countQualifiedBranches(User $user, int $rankLevel): int
    {
        $count = 0;
        $filleuls = $user->filleuls;

        foreach ($filleuls as $filleul) {
            if ($this->hasRankLevel($filleul, $rankLevel)) {
                $count++;
            }
        }

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

        $userLevel = $this->getRankLevel($user->rank);

        // L'utilisateur lui-même a le niveau requis
        if ($userLevel >= $rankLevel) {
            return true;
        }

        // Vérifier récursivement les descendants
        foreach ($user->filleuls as $filleul) {
            if ($this->hasRankLevel($filleul, $rankLevel, $maxDepth, $currentDepth + 1)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir le niveau d'un grade
     */
    private function getRankLevel($rank): int
    {
        if (!$rank) return 1;
        
        if (!is_string($rank) && method_exists($rank, 'getAttribute')) {
            return $rank->level ?? 1;
        }
        
        if (is_string($rank)) {
            $rankModel = Rank::where('name', $rank)->first();
            if ($rankModel) {
                return $rankModel->level;
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