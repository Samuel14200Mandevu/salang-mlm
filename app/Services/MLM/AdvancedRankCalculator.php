<?php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Rank;
use App\Models\QualifiedBranch;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdvancedRankCalculator
{
    /**
     * Calcule le grade avancé de l'utilisateur
     */
    public function calculateAdvancedRank(User $user): ?Rank
    {
        if (!$user->is_active) {
            return null;
        }

        $ranks = Rank::where('is_active', true)
            ->orderBy('level', 'asc')
            ->get();

        $highestRank = null;

        foreach ($ranks as $rank) {
            if ($this->isEligibleForRank($user, $rank)) {
                $highestRank = $rank;
            }
        }

        return $highestRank;
    }

    /**
     * Vérifie si l'utilisateur est éligible pour un grade spécifique
     */
    public function isEligibleForRank(User $user, Rank $rank): bool
    {
        // Vérification du PV minimum
        if ($user->pv_balance < $rank->min_pv) {
            return false;
        }

        // Vérification du BV minimum
        if ($user->bv_balance < $rank->min_bv) {
            return false;
        }

        // Pour les niveaux 1 à 3, c'est simple
        if ($rank->level <= 3) {
            return true;
        }

        // Pour les niveaux 4 à 9, vérifier les conditions complexes
        return $this->checkRankConditions($user, $rank);
    }

    /**
     * Vérifie les conditions complexes d'un grade (Niveaux 4 à 9)
     */
    protected function checkRankConditions(User $user, Rank $rank): bool
    {
        $conditions = $this->getRankConditions($rank);

        if (empty($conditions) || !is_array($conditions)) {
            return true;
        }

        $directChildren = $user->filleuls()->with('rank')->get();

        // Parcourir toutes les conditions
        foreach ($conditions as $condition) {
            if ($this->checkSingleCondition($condition, $directChildren, $user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie une condition simple
     */
    protected function checkSingleCondition(array $condition, $directChildren, User $user): bool
    {
        // ============================================================
        // TYPE 1: PERSONNAL PV (Condition la plus simple)
        // ============================================================
        if (isset($condition['type']) && $condition['type'] === 'personal_pv') {
            $requiredPV = $condition['value'] ?? 0;
            return $user->pv_balance >= $requiredPV;
        }

        // ============================================================
        // TYPE 2: BRANCHES (Ex: 3 branches niveau X avec Y PV)
        // ============================================================
        if (isset($condition['type']) && $condition['type'] === 'branches') {
            $branchLevel = $condition['rank_level'] ?? 0;
            $minBranches = $condition['branches'] ?? 1;
            $minBranchPV = $condition['group_pv'] ?? 0;

            $count = $directChildren->filter(function ($child) use ($branchLevel, $minBranchPV) {
                $childRank = $this->getUserRankObject($child);
                $childRankLevel = $childRank?->level ?? 0;
                return $childRankLevel >= $branchLevel
                    && $child->pv_balance >= $minBranchPV;
            })->count();

            return $count >= $minBranches;
        }

        // ============================================================
        // TYPE 3: BRANCHES MIXED (Ex: 2 branches niveau X + 4 branches niveau Y)
        // ============================================================
        if (isset($condition['type']) && $condition['type'] === 'branches_mixed') {
            $branchRequirements = $condition['branches'] ?? [];
            $minGroupPV = $condition['group_pv'] ?? 0;

            foreach ($branchRequirements as $count => $level) {
                $actualCount = $directChildren->filter(function ($child) use ($level) {
                    $childRank = $this->getUserRankObject($child);
                    return ($childRank?->level ?? 0) >= $level;
                })->count();

                if ($actualCount < $count) {
                    return false;
                }
            }

            $totalGroupPV = $directChildren->sum('pv_balance');

            return $totalGroupPV >= $minGroupPV;
        }

        // ============================================================
        // TYPE 4: OPTIONS (Pour la rétrocompatibilité)
        // ============================================================
        if (isset($condition['type']) && $condition['type'] === 'option') {
            return $this->checkOptionCondition($user, $condition);
        }

        return false;
    }

    /**
     * Vérifie les conditions d'option (Option 1, 2, 3)
     */
    private function checkOptionCondition(User $user, array $condition): bool
    {
        $optionNumber = $condition['option'] ?? 1;
        $directChildren = $user->filleuls()->with('rank')->get();

        switch ($optionNumber) {
            case 1:
                // Option 1: 2 branches niveau X avec Y PV
                $targetLevel = $condition['target_level'] ?? 0;
                $requiredPV = $condition['required_pv'] ?? 0;
                $minBranches = $condition['branches'] ?? 2;

                $count = $directChildren->filter(function ($child) use ($targetLevel, $requiredPV) {
                    $childRank = $this->getUserRankObject($child);
                    return ($childRank?->level ?? 0) >= $targetLevel
                        && $child->pv_balance >= $requiredPV;
                })->count();

                return $count >= $minBranches;

            case 2:
                // Option 2: 2 branches niveau X + 4 branches niveau Y
                $targetLevel = $condition['target_level'] ?? 0;
                $secondaryLevel = $condition['secondary_level'] ?? 0;
                $requiredPV = $condition['required_pv'] ?? 0;

                $targetCount = $directChildren->filter(function ($child) use ($targetLevel, $requiredPV) {
                    $childRank = $this->getUserRankObject($child);
                    return ($childRank?->level ?? 0) >= $targetLevel
                        && $child->pv_balance >= $requiredPV;
                })->count();

                $secondaryCount = $directChildren->filter(function ($child) use ($secondaryLevel, $requiredPV) {
                    $childRank = $this->getUserRankObject($child);
                    return ($childRank?->level ?? 0) >= $secondaryLevel
                        && $child->pv_balance >= $requiredPV;
                })->count();

                return $targetCount >= 2 && $secondaryCount >= 4;

            case 3:
                // Option 3: 1 branche niveau X + 6 branches niveau Y
                $targetLevel = $condition['target_level'] ?? 0;
                $secondaryLevel = $condition['secondary_level'] ?? 0;
                $requiredPV = $condition['required_pv'] ?? 0;

                $targetCount = $directChildren->filter(function ($child) use ($targetLevel, $requiredPV) {
                    $childRank = $this->getUserRankObject($child);
                    return ($childRank?->level ?? 0) >= $targetLevel
                        && $child->pv_balance >= $requiredPV;
                })->count();

                $secondaryCount = $directChildren->filter(function ($child) use ($secondaryLevel, $requiredPV) {
                    $childRank = $this->getUserRankObject($child);
                    return ($childRank?->level ?? 0) >= $secondaryLevel
                        && $child->pv_balance >= $requiredPV;
                })->count();

                return $targetCount >= 1 && $secondaryCount >= 6;

            default:
                return false;
        }
    }

    /**
     * Récupère les conditions du grade depuis la base de données
     */
    private function getRankConditions(Rank $rank): array
    {
        if ($rank->conditions) {
            if (is_string($rank->conditions)) {
                return json_decode($rank->conditions, true) ?? [];
            }
            return $rank->conditions ?? [];
        }

        // Conditions par défaut pour chaque niveau
        return $this->getDefaultConditions($rank->level);
    }

    /**
     * Conditions par défaut pour chaque niveau
     */
    private function getDefaultConditions(int $level): array
    {
        $conditions = [
            // ============================================================
            // NIVEAU 1: Simple inscription
            // ============================================================
            1 => [
                ['type' => 'personal_pv', 'label' => 'Inscription validée', 'value' => 0]
            ],

            // ============================================================
            // NIVEAU 2: Qualification (PV ≥ 100)
            // ============================================================
            2 => [
                ['type' => 'personal_pv', 'label' => 'PV Personnel ≥ 100', 'value' => 100]
            ],

            // ============================================================
            // NIVEAU 3: Cumul Directeur (PV ≥ 200)
            // ============================================================
            3 => [
                ['type' => 'personal_pv', 'label' => 'PV Personnel ≥ 200', 'value' => 200]
            ],

            // ============================================================
            // NIVEAU 4: Directeur (26%)
            // ============================================================
            4 => [
                // Option 1: PV ≥ 1000
                ['type' => 'personal_pv', 'label' => 'PV ≥ 1000', 'value' => 1000],
                // Option 2: 3 branches Niveau 3 avec ≥ 1000 PV
                ['type' => 'branches', 'label' => '3 branches Niveau 3 avec ≥ 1000 PV', 'rank_level' => 3, 'branches' => 3, 'group_pv' => 1000],
                // Option 3: 2 branches Niveau 3 avec ≥ 2200 PV
                ['type' => 'branches', 'label' => '2 branches Niveau 3 avec ≥ 2200 PV', 'rank_level' => 3, 'branches' => 2, 'group_pv' => 2200]
            ],

            // ============================================================
            // NIVEAU 5: Manager Senior (30%)
            // ============================================================
            5 => [
                // Option 1: 3 branches Niveau 4 avec ≥ 3800 PV
                ['type' => 'branches', 'label' => '3 branches Niveau 4 avec ≥ 3800 PV', 'rank_level' => 4, 'branches' => 3, 'group_pv' => 3800],
                // Option 2: 2 branches Niveau 4 avec ≥ 7800 PV
                ['type' => 'branches', 'label' => '2 branches Niveau 4 avec ≥ 7800 PV', 'rank_level' => 4, 'branches' => 2, 'group_pv' => 7800],
                // Option 3: 2 branches Niveau 4 + 4 branches Niveau 3 avec ≥ 3800 PV
                ['type' => 'branches_mixed', 'label' => '2 branches Niveau 4 + 4 branches Niveau 3 avec ≥ 3800 PV', 'branches' => [2 => 4, 4 => 3], 'group_pv' => 3800],
                // Option 4: 1 branche Niveau 4 + 6 branches Niveau 3 avec ≥ 3800 PV
                ['type' => 'branches_mixed', 'label' => '1 branche Niveau 4 + 6 branches Niveau 3 avec ≥ 3800 PV', 'branches' => [1 => 4, 6 => 3], 'group_pv' => 3800]
            ],

            // ============================================================
            // NIVEAU 6: Directeur Envolée (34%)
            // ============================================================
            6 => [
                // Option 1: 3 branches Niveau 5 avec ≥ 16000 PV
                ['type' => 'branches', 'label' => '3 branches Niveau 5 avec ≥ 16000 PV', 'rank_level' => 5, 'branches' => 3, 'group_pv' => 16000],
                // Option 2: 2 branches Niveau 5 avec ≥ 35000 PV
                ['type' => 'branches', 'label' => '2 branches Niveau 5 avec ≥ 35000 PV', 'rank_level' => 5, 'branches' => 2, 'group_pv' => 35000],
                // Option 3: 2 branches Niveau 5 + 4 branches Niveau 4 avec ≥ 16000 PV
                ['type' => 'branches_mixed', 'label' => '2 branches Niveau 5 + 4 branches Niveau 4 avec ≥ 16000 PV', 'branches' => [2 => 5, 4 => 4], 'group_pv' => 16000],
                // Option 4: 1 branche Niveau 5 + 6 branches Niveau 4 avec ≥ 16000 PV
                ['type' => 'branches_mixed', 'label' => '1 branche Niveau 5 + 6 branches Niveau 4 avec ≥ 16000 PV', 'branches' => [1 => 5, 6 => 4], 'group_pv' => 16000]
            ],

            // ============================================================
            // NIVEAU 7: Saphire Manager (40%)
            // ============================================================
            7 => [
                // Option 1: 3 branches Niveau 6 avec ≥ 73000 PV
                ['type' => 'branches', 'label' => '3 branches Niveau 6 avec ≥ 73000 PV', 'rank_level' => 6, 'branches' => 3, 'group_pv' => 73000],
                // Option 2: 2 branches Niveau 6 avec ≥ 145000 PV
                ['type' => 'branches', 'label' => '2 branches Niveau 6 avec ≥ 145000 PV', 'rank_level' => 6, 'branches' => 2, 'group_pv' => 145000],
                // Option 3: 2 branches Niveau 6 + 4 branches Niveau 5 avec ≥ 73000 PV
                ['type' => 'branches_mixed', 'label' => '2 branches Niveau 6 + 4 branches Niveau 5 avec ≥ 73000 PV', 'branches' => [2 => 6, 4 => 5], 'group_pv' => 73000],
                // Option 4: 1 branche Niveau 6 + 6 branches Niveau 5 avec ≥ 73000 PV
                ['type' => 'branches_mixed', 'label' => '1 branche Niveau 6 + 6 branches Niveau 5 avec ≥ 73000 PV', 'branches' => [1 => 6, 6 => 5], 'group_pv' => 73000]
            ],

            // ============================================================
            // NIVEAU 8: Diamant Bleu (43%)
            // ============================================================
            8 => [
                // Option 1: 3 branches Niveau 7 avec ≥ 280000 PV
                ['type' => 'branches', 'label' => '3 branches Niveau 7 avec ≥ 280000 PV', 'rank_level' => 7, 'branches' => 3, 'group_pv' => 280000],
                // Option 2: 2 branches Niveau 7 avec ≥ 580000 PV
                ['type' => 'branches', 'label' => '2 branches Niveau 7 avec ≥ 580000 PV', 'rank_level' => 7, 'branches' => 2, 'group_pv' => 580000],
                // Option 3: 2 branches Niveau 7 + 4 branches Niveau 6 avec ≥ 280000 PV
                ['type' => 'branches_mixed', 'label' => '2 branches Niveau 7 + 4 branches Niveau 6 avec ≥ 280000 PV', 'branches' => [2 => 7, 4 => 6], 'group_pv' => 280000],
                // Option 4: 1 branche Niveau 7 + 6 branches Niveau 6 avec ≥ 280000 PV
                ['type' => 'branches_mixed', 'label' => '1 branche Niveau 7 + 6 branches Niveau 6 avec ≥ 280000 PV', 'branches' => [1 => 7, 6 => 6], 'group_pv' => 280000]
            ],

            // ============================================================
            // NIVEAU 9: Perle Diamant (45%)
            // ============================================================
            9 => [
                // Option 1: 3 branches Niveau 8 avec ≥ 400000 PV
                ['type' => 'branches', 'label' => '3 branches Niveau 8 avec ≥ 400000 PV', 'rank_level' => 8, 'branches' => 3, 'group_pv' => 400000],
                // Option 2: 2 branches Niveau 8 avec ≥ 780000 PV
                ['type' => 'branches', 'label' => '2 branches Niveau 8 avec ≥ 780000 PV', 'rank_level' => 8, 'branches' => 2, 'group_pv' => 780000],
                // Option 3: 2 branches Niveau 8 + 4 branches Niveau 7 avec ≥ 400000 PV
                ['type' => 'branches_mixed', 'label' => '2 branches Niveau 8 + 4 branches Niveau 7 avec ≥ 400000 PV', 'branches' => [2 => 8, 4 => 7], 'group_pv' => 400000],
                // Option 4: 1 branche Niveau 8 + 6 branches Niveau 7 avec ≥ 400000 PV
                ['type' => 'branches_mixed', 'label' => '1 branche Niveau 8 + 6 branches Niveau 7 avec ≥ 400000 PV', 'branches' => [1 => 8, 6 => 7], 'group_pv' => 400000]
            ],

            // ============================================================
            // NIVEAU 10: Pearl (Bonus supplémentaire)
            // ============================================================
            10 => [
                ['type' => 'personal_pv', 'label' => 'PV ≥ 50000', 'value' => 50000]
            ]
        ];

        return $conditions[$level] ?? [];
    }

    /**
     * Obtient l'objet Rank d'un utilisateur
     */
    private function getUserRankObject(User $user): ?Rank
    {
        if ($user->relationLoaded('rank') && $user->rank && !is_string($user->rank)) {
            return $user->rank;
        }

        if ($user->rank_id) {
            return Rank::find($user->rank_id);
        }

        if (is_string($user->rank)) {
            $rank = Rank::where('name', $user->rank)->first();
            if ($rank) {
                return $rank;
            }
            $rank = Rank::where('slug', $user->rank)->first();
            if ($rank) {
                return $rank;
            }
        }

        return Rank::where('level', 1)->first();
    }

    /**
     * Obtient la progression vers le prochain grade
     */
    public function getProgress(User $user): array
    {
        $currentRank = $this->getUserRankObject($user);
        $nextRank = $currentRank ? $currentRank->getNextRank() : Rank::where('level', 1)->first();

        if (!$nextRank) {
            return [
                'current_rank' => $currentRank?->name ?? 'Distributeur',
                'current_level' => $currentRank?->level ?? 1,
                'next_rank' => null,
                'next_level' => null,
                'progress_pv' => 100,
                'progress_percentage' => 100,
                'pv_needed' => 0,
                'total_pv_needed' => 0,
                'current_pv' => $user->pv_balance ?? 0,
                'current_min_pv' => $currentRank?->min_pv ?? 0,
                'next_min_pv' => 0,
            ];
        }

        $currentPV = $user->pv_balance ?? 0;
        $currentMinPV = $currentRank ? $currentRank->min_pv : 0;
        $nextMinPV = $nextRank->min_pv;

        $pvNeeded = max(0, $nextMinPV - $currentPV);
        $totalPVNeeded = max(1, $nextMinPV - $currentMinPV);

        $progressPercentage = min(100, (($currentPV - $currentMinPV) / $totalPVNeeded) * 100);

        return [
            'current_rank' => $currentRank?->name ?? 'Distributeur',
            'current_level' => $currentRank?->level ?? 1,
            'next_rank' => $nextRank->name,
            'next_level' => $nextRank->level,
            'progress_pv' => max(0, $currentPV - $currentMinPV),
            'progress_percentage' => round(max(0, $progressPercentage), 2),
            'pv_needed' => $pvNeeded,
            'total_pv_needed' => $totalPVNeeded,
            'current_pv' => $currentPV,
            'current_min_pv' => $currentMinPV,
            'next_min_pv' => $nextMinPV,
        ];
    }

    /**
     * Calcule les branches qualifiées pour un utilisateur
     */
    public function calculateQualifiedBranches(User $user, string $period): array
    {
        $qualifiedBranches = [];

        $directChildren = $user->filleuls()->with('rank')->get();

        foreach ($directChildren as $child) {
            $childRank = $this->getUserRankObject($child);
            $childRankLevel = $childRank?->level ?? 0;

            if ($childRankLevel >= 3) {
                $branchPV = $this->calculateBranchPV($child);

                QualifiedBranch::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'branch_user_id' => $child->id,
                        'period' => $period,
                    ],
                    [
                        'branch_rank_level' => $childRankLevel,
                        'branch_pv' => $branchPV,
                    ]
                );

                $qualifiedBranches[] = [
                    'user_id' => $child->id,
                    'name' => $child->name,
                    'rank_level' => $childRankLevel,
                    'pv' => $branchPV,
                ];
            }
        }

        return $qualifiedBranches;
    }

    /**
     * Calcule le PV d'une branche (sous-arbre)
     */
    protected function calculateBranchPV(User $branchRoot): int
    {
        $totalPV = $branchRoot->pv_balance;

        $children = $branchRoot->filleuls()->get();
        foreach ($children as $child) {
            $totalPV += $this->calculateBranchPV($child);
        }

        return $totalPV;
    }

    /**
     * Vérifie si un utilisateur est éligible à un grade supérieur
     */
    public function checkHigherRankEligibility(User $user, string $period): array
    {
        $eligibleRanks = [];

        $higherRanks = \App\Models\HigherRank::where('is_active', true)
            ->orderBy('level', 'asc')
            ->get();

        foreach ($higherRanks as $higherRank) {
            if ($higherRank->isEligible($user, $period)) {
                $eligibleRanks[] = [
                    'id' => $higherRank->id,
                    'name' => $higherRank->name,
                    'slug' => $higherRank->slug,
                    'level' => $higherRank->level,
                    'global_bonus_percentage' => $higherRank->global_bonus_percentage,
                ];
            }
        }

        return $eligibleRanks;
    }

    /**
     * Obtient le grade supérieur actuel d'un utilisateur
     */
    public function getCurrentHigherRank(User $user): ?\App\Models\HigherRank
    {
        return $user->higherRanks()
            ->orderBy('level', 'desc')
            ->first();
    }

    /**
     * Compte les branches au niveau 9
     */
    public function countLevel9Branches(User $user, string $period): int
    {
        return QualifiedBranch::where('user_id', $user->id)
            ->where('period', $period)
            ->where('branch_rank_level', 9)
            ->count();
    }

    /**
     * Compte les branches Diamant
     */
    public function countDiamondBranches(User $user, string $period): int
    {
        return QualifiedBranch::where('user_id', $user->id)
            ->where('period', $period)
            ->where('branch_rank_level', '>=', 9)
            ->count();
    }
}