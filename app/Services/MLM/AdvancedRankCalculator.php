<?php
// app/Services/MLM/AdvancedRankCalculator.php

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
            ->orderBy('level', 'desc')
            ->get();

        foreach ($ranks as $rank) {
            if ($this->isEligibleForRank($user, $rank)) {
                return $rank;
            }
        }

        // Retourner le niveau 1 par défaut
        return Rank::where('level', 1)->first();
    }

    /**
     * Vérifie si l'utilisateur est éligible pour un grade spécifique
     */
    public function isEligibleForRank(User $user, Rank $rank): bool
    {
        // Pour les niveaux 1 à 3, c'est simple
        if ($rank->level <= 3) {
            if ($rank->level == 2 && $user->pv_balance < 100) {
                return false;
            }
            if ($rank->level == 3 && $user->pv_balance < 200) {
                return false;
            }
            return true;
        }

        // Pour les niveaux 4 à 9, vérifier les conditions complexes
        return $this->conditionChecker->checkConditions($user, $rank);
    }

    /**
     * Calcule les prix/récompenses pour un utilisateur selon son grade
     */
    public function calculatePrizes(User $user): array
    {
        $prizes = [];
        $rankLevel = $user->rank_level ?? 1;
        
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
                'current_team_pv' => $user->team_pv ?? 0,
                'cumul_pv' => ($user->pv_balance ?? 0) + ($user->team_pv ?? 0),
                'current_min_pv' => $currentRank?->min_pv ?? 0,
                'next_min_pv' => 0,
            ];
        }

        $currentPV = $user->pv_balance ?? 0;
        $currentTeamPV = $user->team_pv ?? 0;
        $cumulPV = $currentPV + $currentTeamPV;
        
        $currentMinPV = $currentRank ? $currentRank->min_pv : 0;
        $nextMinPV = $nextRank->min_pv;

        $pvNeeded = max(0, $nextMinPV - $cumulPV);
        $totalPVNeeded = max(1, $nextMinPV - $currentMinPV);

        $progressPercentage = min(100, (($cumulPV - $currentMinPV) / $totalPVNeeded) * 100);

        return [
            'current_rank' => $currentRank?->name ?? 'Distributeur',
            'current_level' => $currentRank?->level ?? 1,
            'next_rank' => $nextRank->name,
            'next_level' => $nextRank->level,
            'progress_pv' => max(0, $cumulPV - $currentMinPV),
            'progress_percentage' => round(max(0, $progressPercentage), 2),
            'pv_needed' => $pvNeeded,
            'total_pv_needed' => $totalPVNeeded,
            'current_pv' => $currentPV,
            'current_team_pv' => $currentTeamPV,
            'cumul_pv' => $cumulPV,
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
    private function isEligibleForHigherRank(User $user, HigherRank $higherRank, string $period): bool
    {
        $level9Branches = $this->countLevel9Branches($user, $period);
        $diamondBranches = $this->countDiamondBranches($user, $period);

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