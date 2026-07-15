<?php

namespace App\Services;

use App\Models\User;
use App\Models\Rank;
use Illuminate\Support\Facades\DB;

class RankCalculationService
{
    /**
     * Calcule le rang d'un utilisateur pour une période donnée
     */
    public function calculateRank(User $user, string $period): ?Rank
    {
        // 1. Récupérer les données du mois
        $monthlyData = DB::table('user_monthly_ranks')
            ->where('user_id', $user->id)
            ->where('period', $period)
            ->first();

        if (!$monthlyData) {
            return null;
        }

        // 2. Récupérer tous les rangs actifs, du plus haut au plus bas
        $ranks = Rank::where('is_active', true)
            ->orderBy('level', 'desc')
            ->get();

        // 3. Vérifier chaque rang (du plus haut au plus bas)
        foreach ($ranks as $rank) {
            if ($this->meetsRankRequirements($user, $rank, $monthlyData, $period)) {
                return $rank;
            }
        }

        // Si aucun rang n'est atteint, retourner le rang 1 (Distributeur)
        return Rank::where('level', 1)->first();
    }

    /**
     * Vérifie si l'utilisateur remplit les conditions pour un rang donné
     */
    private function meetsRankRequirements(User $user, Rank $rank, $monthlyData, string $period): bool
    {
        $conditions = json_decode($rank->conditions, true);

        if (empty($conditions)) {
            return false;
        }

        foreach ($conditions as $condition) {
            if (!$this->checkCondition($user, $condition, $monthlyData, $period)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifie une condition spécifique
     */
    private function checkCondition(User $user, array $condition, $monthlyData, string $period): bool
    {
        $type = $condition['type'] ?? 'personal_pv';

        switch ($type) {
            case 'personal_pv':
                return $monthlyData->pv_monthly >= ($condition['value'] ?? 0);

            case 'branches':
                // Vérifie le nombre de branches d'un certain niveau
                return $this->checkBranchCondition($user, $condition, $period);

            case 'branches_mixed':
                // Vérifie un mix de branches de différents niveaux
                return $this->checkMixedBranchCondition($user, $condition, $period);

            default:
                return false;
        }
    }

    /**
     * Vérifie la condition "X branches de niveau Y avec PV minimum"
     */
    private function checkBranchCondition(User $user, array $condition, string $period): bool
    {
        $requiredBranches = $condition['branches'] ?? 0;
        $requiredRankLevel = $condition['rank_level'] ?? 0;
        $minGroupPV = $condition['group_pv'] ?? 0;

        // Récupérer les branches qualifiées pour cette période
        $qualifiedBranches = DB::table('qualified_branches')
            ->where('user_id', $user->id)
            ->where('period', $period)
            ->where('branch_rank_level', '>=', $requiredRankLevel)
            ->where('branch_pv', '>=', $minGroupPV)
            ->count();

        return $qualifiedBranches >= $requiredBranches;
    }

    /**
     * Vérifie la condition mixte (ex: 2 branches Niveau 4 + 4 branches Niveau 3)
     */
    private function checkMixedBranchCondition(User $user, array $condition, string $period): bool
    {
        $branchesConfig = $condition['branches'] ?? [];
        $minGroupPV = $condition['group_pv'] ?? 0;

        // Exemple: ['2' => 4, '4' => 3] signifie 2 branches niveau 4 et 4 branches niveau 3
        foreach ($branchesConfig as $requiredCount => $rankLevel) {
            $count = DB::table('qualified_branches')
                ->where('user_id', $user->id)
                ->where('period', $period)
                ->where('branch_rank_level', '>=', $rankLevel)
                ->where('branch_pv', '>=', $minGroupPV)
                ->count();

            if ($count < $requiredCount) {
                return false;
            }
        }

        return true;
    }
}