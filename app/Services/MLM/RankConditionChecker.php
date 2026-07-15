<?php
// app/Services/MLM/RankConditionChecker.php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Rank;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Inscription (toujours vrai)
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
     */
    private function checkLevel4(User $user): bool
    {
        // Option 1: PV ≥ 1000
        if ($user->pv_balance >= 1000) {
            return true;
        }

        $branchesNiveau3 = $this->countQualifiedBranches($user, 3);
        $groupPV = $this->getGroupPV($user);

        // Option 2: 3 branches Niveau 3 + PV groupe ≥ 1000
        if ($branchesNiveau3 >= 3 && $groupPV >= 1000) {
            return true;
        }

        // Option 3: 2 branches Niveau 3 + PV groupe ≥ 2200
        if ($branchesNiveau3 >= 2 && $groupPV >= 2200) {
            return true;
        }

        return false;
    }

    /**
     * Niveau 5 : Manager Senior
     */
    private function checkLevel5(User $user): bool
    {
        $branchesNiveau4 = $this->countQualifiedBranches($user, 4);
        $branchesNiveau3 = $this->countQualifiedBranches($user, 3);
        $groupPV = $this->getGroupPV($user);

        // Option 1: 3 branches Niveau 4 + PV groupe ≥ 3800
        if ($branchesNiveau4 >= 3 && $groupPV >= 3800) {
            return true;
        }

        // Option 2: 2 branches Niveau 4 + PV groupe ≥ 7800
        if ($branchesNiveau4 >= 2 && $groupPV >= 7800) {
            return true;
        }

        // Option 3: 2 branches Niveau 4 + 4 branches Niveau 3 + PV groupe ≥ 3800
        if ($branchesNiveau4 >= 2 && $branchesNiveau3 >= 4 && $groupPV >= 3800) {
            return true;
        }

        // Option 4: 1 branche Niveau 4 + 6 branches Niveau 3 + PV groupe ≥ 3800
        if ($branchesNiveau4 >= 1 && $branchesNiveau3 >= 6 && $groupPV >= 3800) {
            return true;
        }

        return false;
    }

    /**
     * Niveau 6 : Directeur Envolée
     */
    private function checkLevel6(User $user): bool
    {
        $branchesNiveau5 = $this->countQualifiedBranches($user, 5);
        $branchesNiveau4 = $this->countQualifiedBranches($user, 4);
        $groupPV = $this->getGroupPV($user);

        // Option 1: 3 branches Niveau 5 + PV groupe ≥ 16000
        if ($branchesNiveau5 >= 3 && $groupPV >= 16000) {
            return true;
        }

        // Option 2: 2 branches Niveau 5 + PV groupe ≥ 35000
        if ($branchesNiveau5 >= 2 && $groupPV >= 35000) {
            return true;
        }

        // Option 3: 2 branches Niveau 5 + 4 branches Niveau 4 + PV groupe ≥ 16000
        if ($branchesNiveau5 >= 2 && $branchesNiveau4 >= 4 && $groupPV >= 16000) {
            return true;
        }

        // Option 4: 1 branche Niveau 5 + 6 branches Niveau 4 + PV groupe ≥ 16000
        if ($branchesNiveau5 >= 1 && $branchesNiveau4 >= 6 && $groupPV >= 16000) {
            return true;
        }

        return false;
    }

    /**
     * Niveau 7 : Saphire Manager
     */
    private function checkLevel7(User $user): bool
    {
        $branchesNiveau6 = $this->countQualifiedBranches($user, 6);
        $branchesNiveau5 = $this->countQualifiedBranches($user, 5);
        $groupPV = $this->getGroupPV($user);

        // Option 1: 3 branches Niveau 6 + PV groupe ≥ 73000
        if ($branchesNiveau6 >= 3 && $groupPV >= 73000) {
            return true;
        }

        // Option 2: 2 branches Niveau 6 + PV groupe ≥ 145000
        if ($branchesNiveau6 >= 2 && $groupPV >= 145000) {
            return true;
        }

        // Option 3: 2 branches Niveau 6 + 4 branches Niveau 5 + PV groupe ≥ 73000
        if ($branchesNiveau6 >= 2 && $branchesNiveau5 >= 4 && $groupPV >= 73000) {
            return true;
        }

        // Option 4: 1 branche Niveau 6 + 6 branches Niveau 5 + PV groupe ≥ 73000
        if ($branchesNiveau6 >= 1 && $branchesNiveau5 >= 6 && $groupPV >= 73000) {
            return true;
        }

        return false;
    }

    /**
     * Niveau 8 : Diamant Bleu
     */
    private function checkLevel8(User $user): bool
    {
        $branchesNiveau7 = $this->countQualifiedBranches($user, 7);
        $branchesNiveau6 = $this->countQualifiedBranches($user, 6);
        $groupPV = $this->getGroupPV($user);

        // Option 1: 3 branches Niveau 7 + PV groupe ≥ 280000
        if ($branchesNiveau7 >= 3 && $groupPV >= 280000) {
            return true;
        }

        // Option 2: 2 branches Niveau 7 + PV groupe ≥ 580000
        if ($branchesNiveau7 >= 2 && $groupPV >= 580000) {
            return true;
        }

        // Option 3: 2 branches Niveau 7 + 4 branches Niveau 6 + PV groupe ≥ 280000
        if ($branchesNiveau7 >= 2 && $branchesNiveau6 >= 4 && $groupPV >= 280000) {
            return true;
        }

        // Option 4: 1 branche Niveau 7 + 6 branches Niveau 6 + PV groupe ≥ 280000
        if ($branchesNiveau7 >= 1 && $branchesNiveau6 >= 6 && $groupPV >= 280000) {
            return true;
        }

        return false;
    }

    /**
     * Niveau 9 : Perle Diamant
     */
    private function checkLevel9(User $user): bool
    {
        $branchesNiveau8 = $this->countQualifiedBranches($user, 8);
        $branchesNiveau7 = $this->countQualifiedBranches($user, 7);
        $groupPV = $this->getGroupPV($user);

        // Option 1: 3 branches Niveau 8 + PV groupe ≥ 400000
        if ($branchesNiveau8 >= 3 && $groupPV >= 400000) {
            return true;
        }

        // Option 2: 2 branches Niveau 8 + PV groupe ≥ 780000
        if ($branchesNiveau8 >= 2 && $groupPV >= 780000) {
            return true;
        }

        // Option 3: 2 branches Niveau 8 + 4 branches Niveau 7 + PV groupe ≥ 400000
        if ($branchesNiveau8 >= 2 && $branchesNiveau7 >= 4 && $groupPV >= 400000) {
            return true;
        }

        // Option 4: 1 branche Niveau 8 + 6 branches Niveau 7 + PV groupe ≥ 400000
        if ($branchesNiveau8 >= 1 && $branchesNiveau7 >= 6 && $groupPV >= 400000) {
            return true;
        }

        return false;
    }

    /**
     * Compter les branches qualifiées pour un niveau donné
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
     * ✅ CORRIGÉ - Avec protection contre les boucles infinies
     */
    private function hasRankLevel(User $user, int $rankLevel, int $maxDepth = 20, int $currentDepth = 0): bool
    {
        // ✅ Protection contre les boucles infinies
        if ($currentDepth >= $maxDepth) {
            Log::warning('Max depth reached in hasRankLevel', [
                'user_id' => $user->id,
                'depth' => $currentDepth,
                'rank_level' => $rankLevel
            ]);
            return false;
        }

        $userLevel = $this->getRankLevel($user->rank);

        // L'utilisateur lui-même a le niveau requis
        if ($userLevel >= $rankLevel) {
            return true;
        }

        // ✅ Vérifier récursivement les descendants avec limite de profondeur
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
        
        // Si c'est un objet Rank
        if (!is_string($rank) && method_exists($rank, 'getAttribute')) {
            return $rank->level ?? 1;
        }
        
        // Si c'est une string (nom du grade)
        if (is_string($rank)) {
            $rankModel = Rank::where('name', $rank)->first();
            if ($rankModel) {
                return $rankModel->level;
            }
        }
        
        return 1;
    }

    /**
     * Calculer le PV total du groupe (descendants)
     */
    private function getGroupPV(User $user): int
    {
        $totalPV = 0;
        $descendants = $this->getAllDescendants($user);

        foreach ($descendants as $descendant) {
            $totalPV += $descendant->pv_balance;
        }

        return $totalPV;
    }

    /**
     * Récupérer tous les descendants (récursif)
     * ✅ CORRIGÉ - Avec protection contre les boucles infinies
     */
    private function getAllDescendants(User $user, int $maxDepth = 20, int $currentDepth = 0): array
    {
        // ✅ Protection contre les boucles infinies
        if ($currentDepth >= $maxDepth) {
            Log::warning('Max depth reached in getAllDescendants', [
                'user_id' => $user->id,
                'depth' => $currentDepth
            ]);
            return [];
        }
        
        $descendants = [];
        $processed = [$user->id];

        foreach ($user->filleuls as $filleul) {
            // ✅ Éviter les boucles infinies
            if (in_array($filleul->id, $processed)) {
                continue;
            }
            $processed[] = $filleul->id;
            $descendants[] = $filleul;
            $descendants = array_merge($descendants, $this->getAllDescendants($filleul, $maxDepth, $currentDepth + 1));
        }

        return $descendants;
    }

    /**
     *  Vider le cache interne si nécessaire
     */
    public function clearCache(): void
    {
        // Si vous utilisez un cache, vous pouvez le vider ici
         Cache::forget('rank_conditions_cache');
    }
}