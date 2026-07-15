<?php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Rank;
use App\Models\QualifiedBranch;
use App\Models\HigherRank;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdvancedRankCalculator
{
    /**
     * @var RankConditionChecker
     */
    protected RankConditionChecker $conditionChecker;

    /**
     * @var array Cache pour les PV des branches
     */
    protected array $branchPVCache = [];

    /**
     * @var array Cache pour les descendants
     */
    protected array $descendantsCache = [];

    /**
     * Constructor
     */
    public function __construct(RankConditionChecker $conditionChecker)
    {
        $this->conditionChecker = $conditionChecker;
    }

    /**
     * Calcule le grade avancé de l'utilisateur
     */
    public function calculateAdvancedRank(User $user): ?Rank
    {
        if (!$user->is_active) {
            return null;
        }

        // Vider le cache pour ce calcul
        $this->clearCache();

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
        // TYPE 1: PERSONAL PV
        // ============================================================
        if (isset($condition['type']) && $condition['type'] === 'personal_pv') {
            $requiredPV = $condition['value'] ?? 0;
            return $user->pv_balance >= $requiredPV;
        }

        // ============================================================
        // TYPE 2: BRANCHES (CUMUL = PV du SPONSOR + PV des BRANCHES)
        // ============================================================
        if (isset($condition['type']) && $condition['type'] === 'branches') {
            $branchLevel = $condition['rank_level'] ?? 0;
            $minBranches = $condition['branches'] ?? 1;
            $minCumulPV = $condition['group_pv'] ?? 0;

            $qualifiedBranches = 0;
            $cumulPV = $user->pv_balance;

            foreach ($directChildren as $child) {
                $childRank = $this->getUserRankObject($child);
                $childRankLevel = $childRank?->level ?? 0;
                
                if ($childRankLevel >= $branchLevel) {
                    $qualifiedBranches++;
                    $childPV = $this->getBranchTotalPV($child);
                    $cumulPV += $childPV;
                }
            }

            return $qualifiedBranches >= $minBranches && $cumulPV >= $minCumulPV;
        }

        // ============================================================
        // TYPE 3: BRANCHES MIXED (CUMUL = PV du SPONSOR + PV des BRANCHES)
        // ============================================================
        if (isset($condition['type']) && $condition['type'] === 'branches_mixed') {
            $branchRequirements = $condition['branches'] ?? [];
            $minCumulPV = $condition['group_pv'] ?? 0;

            $cumulPV = $user->pv_balance;
            $allConditionsMet = true;

            foreach ($branchRequirements as $requiredCount => $requiredLevel) {
                $actualCount = 0;
                foreach ($directChildren as $child) {
                    $childRank = $this->getUserRankObject($child);
                    if (($childRank?->level ?? 0) >= $requiredLevel) {
                        $actualCount++;
                        $childPV = $this->getBranchTotalPV($child);
                        $cumulPV += $childPV;
                    }
                }

                if ($actualCount < $requiredCount) {
                    $allConditionsMet = false;
                    break;
                }
            }

            if (!$allConditionsMet) {
                return false;
            }

            return $cumulPV >= $minCumulPV;
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
                $targetLevel = $condition['target_level'] ?? 0;
                $requiredPV = $condition['required_pv'] ?? 0;
                $minBranches = $condition['branches'] ?? 2;

                $cumulPV = $user->pv_balance;
                $qualifiedBranches = 0;

                foreach ($directChildren as $child) {
                    $childRank = $this->getUserRankObject($child);
                    if (($childRank?->level ?? 0) >= $targetLevel) {
                        $qualifiedBranches++;
                        $childPV = $this->getBranchTotalPV($child);
                        $cumulPV += $childPV;
                    }
                }

                return $qualifiedBranches >= $minBranches && $cumulPV >= $requiredPV;

            case 2:
                $targetLevel = $condition['target_level'] ?? 0;
                $secondaryLevel = $condition['secondary_level'] ?? 0;
                $requiredPV = $condition['required_pv'] ?? 0;

                $cumulPV = $user->pv_balance;
                $targetCount = 0;
                $secondaryCount = 0;

                foreach ($directChildren as $child) {
                    $childRank = $this->getUserRankObject($child);
                    $childPV = $this->getBranchTotalPV($child);
                    
                    if (($childRank?->level ?? 0) >= $targetLevel) {
                        $targetCount++;
                        $cumulPV += $childPV;
                    }
                    
                    if (($childRank?->level ?? 0) >= $secondaryLevel) {
                        $secondaryCount++;
                    }
                }

                return $targetCount >= 2 && $secondaryCount >= 4 && $cumulPV >= $requiredPV;

            case 3:
                $targetLevel = $condition['target_level'] ?? 0;
                $secondaryLevel = $condition['secondary_level'] ?? 0;
                $requiredPV = $condition['required_pv'] ?? 0;

                $cumulPV = $user->pv_balance;
                $targetCount = 0;
                $secondaryCount = 0;

                foreach ($directChildren as $child) {
                    $childRank = $this->getUserRankObject($child);
                    $childPV = $this->getBranchTotalPV($child);
                    
                    if (($childRank?->level ?? 0) >= $targetLevel) {
                        $targetCount++;
                        $cumulPV += $childPV;
                    }
                    
                    if (($childRank?->level ?? 0) >= $secondaryLevel) {
                        $secondaryCount++;
                    }
                }

                return $targetCount >= 1 && $secondaryCount >= 6 && $cumulPV >= $requiredPV;

            default:
                return false;
        }
    }

    /**
     * Calcule le PV total d'une branche (chef + tous ses descendants)
     * Version optimisée avec cache
     */
    private function getBranchTotalPV(User $branchRoot): int
    {
        $cacheKey = 'branch_pv_' . $branchRoot->id;
        
        if (isset($this->branchPVCache[$cacheKey])) {
            return $this->branchPVCache[$cacheKey];
        }

        $totalPV = $branchRoot->pv_balance;
        
        $descendants = $this->getAllDescendantsOptimized($branchRoot);
        foreach ($descendants as $descendant) {
            $totalPV += $descendant->pv_balance;
        }
        
        $this->branchPVCache[$cacheKey] = $totalPV;
        
        return $totalPV;
    }

    /**
     * Récupère tous les descendants d'un utilisateur (version optimisée)
     */
    private function getAllDescendantsOptimized(User $user): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = 'descendants_' . $user->id;
        
        if (isset($this->descendantsCache[$cacheKey])) {
            return $this->descendantsCache[$cacheKey];
        }

        $ids = [$user->id];
        $allDescendantIds = [];
        $maxDepth = 20; // Limite de sécurité
        $currentDepth = 0;
        
        while (!empty($ids) && $currentDepth < $maxDepth) {
            $children = User::whereIn('parrain_id', $ids)
                ->where('is_active', true)
                ->select('id', 'pv_balance')
                ->get();
                
            if ($children->isEmpty()) {
                break;
            }
            
            $ids = $children->pluck('id')->toArray();
            $allDescendantIds = array_merge($allDescendantIds, $ids);
            $currentDepth++;
        }
        
        $result = User::whereIn('id', $allDescendantIds)->select('pv_balance')->get();
        $this->descendantsCache[$cacheKey] = $result;
        
        return $result;
    }

    /**
     * Récupère tous les descendants d'un utilisateur (méthode récursive avec limite)
     * Conservée pour compatibilité mais dépréciée
     * 
     * @deprecated Utilisez getAllDescendantsOptimized() à la place
     */
    private function getAllDescendants(User $user): array
    {
        $descendants = [];
        $this->getDescendantsRecursive($user, $descendants, 0, 20);
        return $descendants;
    }

    /**
     * Calcule les prix/récompenses pour un utilisateur selon son grade
     */
    public function calculatePrizes(User $user): array
    {
        $prizes = [];
        $rankLevel = $user->rank?->level ?? 1;
        
        if ($rankLevel >= 4) {
            $prizes[] = ['level' => 4, 'prize' => 'Manager - Woofer'];
        }
        if ($rankLevel >= 5) {
            $prizes[] = ['level' => 5, 'prize' => 'Directeur Principal - LCD TV'];
        }
        if ($rankLevel >= 6) {
            $prizes[] = ['level' => 6, 'prize' => 'Soaring Manager - Moto'];
        }
        if ($rankLevel >= 7) {
            $prizes[] = ['level' => 7, 'prize' => 'Saphire Manager - Petite voiture'];
        }
        if ($rankLevel >= 8) {
            $prizes[] = ['level' => 8, 'prize' => 'Blue Diamond - Grande voiture'];
        }
        if ($rankLevel >= 9) {
            $prizes[] = ['level' => 9, 'prize' => 'Diamond Pearl - House'];
        }
        
        return $prizes;
    }

    /**
     * Récupère récursivement les descendants avec limite de profondeur
     */
    private function getDescendantsRecursive($user, array &$descendants, int $currentDepth = 0, int $maxDepth = 20): void
    {
        // Protection contre les récursions infinies
        if ($currentDepth >= $maxDepth) {
            Log::warning('Max depth reached in getDescendantsRecursive', [
                'user_id' => $user->id,
                'depth' => $currentDepth
            ]);
            return;
        }

        $children = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($children as $child) {
            $descendants[] = $child;
            $this->getDescendantsRecursive($child, $descendants, $currentDepth + 1, $maxDepth);
        }
    }

    /**
     * Récupère les conditions du grade depuis la base de données
     */
    private function getRankConditions(Rank $rank): array
    {
        if ($rank->conditions) {
            if (is_string($rank->conditions)) {
                $conditions = json_decode($rank->conditions, true);
                
                // Si le décodage échoue, utiliser les conditions par défaut
                if ($conditions === null && json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('Invalid JSON in rank conditions', [
                        'rank_id' => $rank->id,
                        'rank_level' => $rank->level,
                        'error' => json_last_error_msg()
                    ]);
                    return $this->getDefaultConditions($rank->level);
                }
                
                return $conditions ?? [];
            }
            return $rank->conditions ?? [];
        }

        return $this->getDefaultConditions($rank->level);
    }

    /**
     * Conditions par défaut pour chaque niveau
     */
    private function getDefaultConditions(int $level): array
    {
        $conditions = [
            // NIVEAU 1
            1 => [
                ['type' => 'personal_pv', 'label' => 'Inscription validée', 'value' => 0]
            ],

            // NIVEAU 2
            2 => [
                ['type' => 'personal_pv', 'label' => 'PV Personnel ≥ 100', 'value' => 100]
            ],

            // NIVEAU 3
            3 => [
                ['type' => 'personal_pv', 'label' => 'PV Personnel ≥ 200', 'value' => 200]
            ],

            // NIVEAU 4
            4 => [
                ['type' => 'personal_pv', 'label' => 'PV ≥ 1000', 'value' => 1000],
                ['type' => 'branches', 'label' => '3 branches Niveau 3 avec CUMUL ≥ 1000 PV', 'rank_level' => 3, 'branches' => 3, 'group_pv' => 1000],
                ['type' => 'branches', 'label' => '2 branches Niveau 3 avec CUMUL ≥ 2200 PV', 'rank_level' => 3, 'branches' => 2, 'group_pv' => 2200]
            ],

            // NIVEAU 5
            5 => [
                ['type' => 'branches', 'label' => '3 branches Niveau 4 avec CUMUL ≥ 3800 PV', 'rank_level' => 4, 'branches' => 3, 'group_pv' => 3800],
                ['type' => 'branches', 'label' => '2 branches Niveau 4 avec CUMUL ≥ 7800 PV', 'rank_level' => 4, 'branches' => 2, 'group_pv' => 7800],
                ['type' => 'branches_mixed', 'label' => '2 branches Niveau 4 + 4 branches Niveau 3 avec CUMUL ≥ 3800 PV', 'branches' => [2 => 4, 4 => 3], 'group_pv' => 3800],
                ['type' => 'branches_mixed', 'label' => '1 branche Niveau 4 + 6 branches Niveau 3 avec CUMUL ≥ 3800 PV', 'branches' => [1 => 4, 6 => 3], 'group_pv' => 3800]
            ],

            // NIVEAU 6
            6 => [
                ['type' => 'branches', 'label' => '3 branches Niveau 5 avec CUMUL ≥ 16000 PV', 'rank_level' => 5, 'branches' => 3, 'group_pv' => 16000],
                ['type' => 'branches', 'label' => '2 branches Niveau 5 avec CUMUL ≥ 35000 PV', 'rank_level' => 5, 'branches' => 2, 'group_pv' => 35000],
                ['type' => 'branches_mixed', 'label' => '2 branches Niveau 5 + 4 branches Niveau 4 avec CUMUL ≥ 16000 PV', 'branches' => [2 => 5, 4 => 4], 'group_pv' => 16000],
                ['type' => 'branches_mixed', 'label' => '1 branche Niveau 5 + 6 branches Niveau 4 avec CUMUL ≥ 16000 PV', 'branches' => [1 => 5, 6 => 4], 'group_pv' => 16000]
            ],

            // NIVEAU 7
            7 => [
                ['type' => 'branches', 'label' => '3 branches Niveau 6 avec CUMUL ≥ 73000 PV', 'rank_level' => 6, 'branches' => 3, 'group_pv' => 73000],
                ['type' => 'branches', 'label' => '2 branches Niveau 6 avec CUMUL ≥ 145000 PV', 'rank_level' => 6, 'branches' => 2, 'group_pv' => 145000],
                ['type' => 'branches_mixed', 'label' => '2 branches Niveau 6 + 4 branches Niveau 5 avec CUMUL ≥ 73000 PV', 'branches' => [2 => 6, 4 => 5], 'group_pv' => 73000],
                ['type' => 'branches_mixed', 'label' => '1 branche Niveau 6 + 6 branches Niveau 5 avec CUMUL ≥ 73000 PV', 'branches' => [1 => 6, 6 => 5], 'group_pv' => 73000]
            ],

            // NIVEAU 8
            8 => [
                ['type' => 'branches', 'label' => '3 branches Niveau 7 avec CUMUL ≥ 280000 PV', 'rank_level' => 7, 'branches' => 3, 'group_pv' => 280000],
                ['type' => 'branches', 'label' => '2 branches Niveau 7 avec CUMUL ≥ 580000 PV', 'rank_level' => 7, 'branches' => 2, 'group_pv' => 580000],
                ['type' => 'branches_mixed', 'label' => '2 branches Niveau 7 + 4 branches Niveau 6 avec CUMUL ≥ 280000 PV', 'branches' => [2 => 7, 4 => 6], 'group_pv' => 280000],
                ['type' => 'branches_mixed', 'label' => '1 branche Niveau 7 + 6 branches Niveau 6 avec CUMUL ≥ 280000 PV', 'branches' => [1 => 7, 6 => 6], 'group_pv' => 280000]
            ],

            // NIVEAU 9
            9 => [
                ['type' => 'branches', 'label' => '3 branches Niveau 8 avec CUMUL ≥ 400000 PV', 'rank_level' => 8, 'branches' => 3, 'group_pv' => 400000],
                ['type' => 'branches', 'label' => '2 branches Niveau 8 avec CUMUL ≥ 780000 PV', 'rank_level' => 8, 'branches' => 2, 'group_pv' => 780000],
                ['type' => 'branches_mixed', 'label' => '2 branches Niveau 8 + 4 branches Niveau 7 avec CUMUL ≥ 400000 PV', 'branches' => [2 => 8, 4 => 7], 'group_pv' => 400000],
                ['type' => 'branches_mixed', 'label' => '1 branche Niveau 8 + 6 branches Niveau 7 avec CUMUL ≥ 400000 PV', 'branches' => [1 => 8, 6 => 7], 'group_pv' => 400000]
            ],
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
        $nextRank = $currentRank ? $this->getNextRank($currentRank) : Rank::where('level', 1)->first();

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
     * Obtient le prochain grade
     */
    private function getNextRank(Rank $currentRank): ?Rank
    {
        return Rank::where('level', '>', $currentRank->level)
            ->where('is_active', true)
            ->orderBy('level', 'asc')
            ->first();
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
                $branchPV = $this->calculateBranchPVOptimized($child);

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
     * Calcule le PV d'une branche (version optimisée)
     */
    protected function calculateBranchPVOptimized(User $branchRoot): int
    {
        $totalPV = $branchRoot->pv_balance;

        $children = $branchRoot->filleuls()->get();
        foreach ($children as $child) {
            $totalPV += $this->calculateBranchPVOptimized($child);
        }

        return $totalPV;
    }

    /**
     * Calcule le PV d'une branche (version récursive - dépréciée)
     * 
     * @deprecated Utilisez calculateBranchPVOptimized() à la place
     */
    protected function calculateBranchPV(User $branchRoot): int
    {
        return $this->calculateBranchPVOptimized($branchRoot);
    }

    /**
     * Vérifie si un utilisateur est éligible à un grade supérieur
     */
    public function checkHigherRankEligibility(User $user, string $period): array
    {
        $eligibleRanks = [];

        $higherRanks = HigherRank::where('is_active', true)
            ->orderBy('level', 'asc')
            ->get();

        foreach ($higherRanks as $higherRank) {
            if ($this->isEligibleForHigherRank($user, $higherRank, $period)) {
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
     * Vérifie si un utilisateur est éligible à un grade supérieur spécifique
     */
   // ✅ CORRECTION
private function isEligibleForHigherRank(User $user, HigherRank $higherRank, string $period): bool
{
    $level9Branches = $this->countLevel9Branches($user, $period);
    $diamondBranches = $this->countDiamondBranches($user, $period);

    // Utiliser le niveau plutôt que le slug
    switch ($higherRank->level) {
        case 1: // Rubis
            return $level9Branches >= 2;
        case 2: // Saphir
            return $level9Branches >= 3;
        case 3: // Diamant 1
            return $level9Branches >= 4;
        case 4: // Diamant 2
            return $level9Branches >= 5;
        case 5: // Diamant 3
            return $level9Branches >= 6;
        case 6: // Diamant 4
            return $level9Branches >= 7;
        case 7: // Diamant 5
            return $level9Branches >= 8;
        case 8: // Actionnaire
            return $diamondBranches >= 4;
        default:
            return false;
    }
}

    /**
     * Obtient le grade supérieur actuel d'un utilisateur
     */
    public function getCurrentHigherRank(User $user): ?HigherRank
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
            ->where('branch_rank_level', '>=', 9)
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

    /**
     * Vide le cache interne
     */
    protected function clearCache(): void
    {
        $this->branchPVCache = [];
        $this->descendantsCache = [];
    }
}