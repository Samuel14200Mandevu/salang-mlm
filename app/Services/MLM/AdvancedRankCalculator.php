<?php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Rank;
use App\Models\RankHistory;
use App\Models\QualifiedBranch;
use App\Models\HigherRank;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdvancedRankCalculator
{
    protected RankConditionChecker $conditionChecker;
    protected array $branchPVCache = [];
    protected array $descendantsCache = [];

    public function __construct(RankConditionChecker $conditionChecker)
    {
        $this->conditionChecker = $conditionChecker;
    }

    public function calculateAdvancedRank(User $user): ?Rank
    {
        if (!$user->is_active) {
            Log::info('User inactive, skipping rank calculation', ['user_id' => $user->id]);
            return null;
        }

        $this->clearCache();

        $ranks = Rank::where('is_active', true)->orderBy('level', 'desc')->get();

        if ($ranks->isEmpty()) {
            Log::warning('No active ranks found');
            return Rank::where('level', 1)->first();
        }

        Log::info('Calculating rank for user', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'pv_balance' => $user->pv_balance,
            'team_pv' => $user->team_pv,
            'current_rank' => $user->rank ?? 'None',
        ]);

        foreach ($ranks as $rank) {
            if ($this->isEligibleForRank($user, $rank)) {
                Log::info('Rank found for user', [
                    'user_id' => $user->id,
                    'rank_id' => $rank->id,
                    'rank_name' => $rank->name,
                    'rank_level' => $rank->level,
                ]);
                return $rank;
            }
        }

        Log::info('No rank found, returning default level 1', ['user_id' => $user->id]);
        return Rank::where('level', 1)->first();
    }

    /**
     * RECALCUL DU TEAM_PV AVEC TOUS LES DESCENDANTS (RECURSIF)
     */
    public function updateTeamPV(User $user): void
    {
        $teamData = $this->calculateTeamPVRecursive($user);
        
        $user->team_pv = $teamData['pv'];
        $user->team_bv = $teamData['bv'];
        $user->total_team = $teamData['total'];
        $user->saveQuietly();
        
        Log::debug('Team PV mis a jour avec tous les descendants', [
            'user_id' => $user->id,
            'team_pv' => $teamData['pv'],
            'total_team' => $teamData['total'],
        ]);
    }

    /**
     * CALCUL RECURSIF DU TEAM_PV AVEC TOUS LES DESCENDANTS
     */
    private function calculateTeamPVRecursive(User $user): array
    {
        $totalPV = $user->pv_balance ?? 0;
        $totalBV = $user->bv_balance ?? 0;
        $totalCount = 0;

        $filleuls = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($filleuls as $filleul) {
            $childData = $this->calculateTeamPVRecursive($filleul);
            $totalPV += $childData['pv'];
            $totalBV += $childData['bv'];
            $totalCount += 1 + $childData['total'];
        }

        return [
            'pv' => $totalPV,
            'bv' => $totalBV,
            'total' => $totalCount,
        ];
    }

    public function recalculateUserRank(User $user, ?string $reason = null): void
    {
        if (!$user->is_active) {
            Log::info('User inactive, skipping rank recalculation', ['user_id' => $user->id]);
            return;
        }

        try {
            $oldRankId = $user->rank_id;
            $oldRankName = $user->rank ?? 'Distributeur';

            $this->updateTeamPV($user);

            $newRank = $this->calculateAdvancedRank($user);

            if ($newRank && $newRank->id != $oldRankId) {
                $user->rank_id = $newRank->id;
                $user->rank = $newRank->name;
                $user->rank_level = $newRank->level;
                $user->last_rank_update = now();
                $user->saveQuietly();

                RankHistory::create([
                    'user_id' => $user->id,
                    'old_rank_id' => $oldRankId,
                    'new_rank_id' => $newRank->id,
                    'old_rank_name' => $oldRankName,
                    'new_rank_name' => $newRank->name,
                    'pv_at_time' => $user->pv_balance,
                    'bv_at_time' => $user->bv_balance,
                    'monthly_pv_at_time' => $user->monthly_pv,
                    'notes' => $reason ?? 'Recalcul automatique du grade',
                ]);

                Log::info('Grade mis a jour via recalcul centralise', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'old_rank' => $oldRankName,
                    'new_rank' => $newRank->name,
                    'new_level' => $newRank->level,
                    'reason' => $reason,
                ]);
            }

            $this->clearUserCache($user);

        } catch (\Exception $e) {
            Log::error('Erreur lors du recalcul du grade', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function recalculateUserRankLight(User $user, ?string $reason = null): void
    {
        if (!$user->is_active) {
            Log::info('User inactive, skipping rank recalculation', ['user_id' => $user->id]);
            return;
        }

        try {
            $oldRankId = $user->rank_id;
            $oldRankName = $user->rank ?? 'Distributeur';

            $newRank = $this->calculateAdvancedRank($user);

            if ($newRank && $newRank->id != $oldRankId) {
                $user->rank_id = $newRank->id;
                $user->rank = $newRank->name;
                $user->rank_level = $newRank->level;
                $user->last_rank_update = now();
                $user->saveQuietly();

                RankHistory::create([
                    'user_id' => $user->id,
                    'old_rank_id' => $oldRankId,
                    'new_rank_id' => $newRank->id,
                    'old_rank_name' => $oldRankName,
                    'new_rank_name' => $newRank->name,
                    'pv_at_time' => $user->pv_balance,
                    'bv_at_time' => $user->bv_balance,
                    'monthly_pv_at_time' => $user->monthly_pv,
                    'notes' => $reason ?? 'Recalcul leger du grade',
                ]);

                Log::info('Grade mis a jour (recalcul leger)', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'old_rank' => $oldRankName,
                    'new_rank' => $newRank->name,
                    'new_level' => $newRank->level,
                ]);
            }

            $this->clearUserCache($user);

        } catch (\Exception $e) {
            Log::error('Erreur lors du recalcul leger du grade', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function recalculateUserRankWithAncestors(User $user, int $ancestorDepth = 3, ?string $reason = null): void
    {
        $this->recalculateUserRank($user, $reason);

        $current = $user->parrain;
        $level = 1;
        $processed = [];

        while ($current && $level <= $ancestorDepth && !in_array($current->id, $processed)) {
            $processed[] = $current->id;
            $this->recalculateUserRank($current, $reason ? "Ancetre - {$reason}" : "Mise a jour automatique d'un descendant");
            $current->refresh();
            $this->clearUserCache($current);
            $current = $current->parrain;
            $level++;
        }
    }

    private function clearUserCache(User $user): void
    {
        Cache::forget("user_rank_{$user->id}");
        Cache::forget("rank_calculation_{$user->id}");
        Cache::forget("descendants_{$user->id}");
        Cache::forget("descendants_count_{$user->id}");
        $this->clearCache();
        $this->conditionChecker->clearCache();
    }

    public function isEligibleForRank(User $user, Rank $rank): bool
    {
        if ($rank->level <= 3) {
            if ($rank->level == 2 && ($user->pv_balance ?? 0) < 100) {
                return false;
            }
            if ($rank->level == 3 && ($user->pv_balance ?? 0) < 200) {
                return false;
            }
            return true;
        }
        return $this->conditionChecker->checkConditions($user, $rank);
    }

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

    private function getNextRank(Rank $currentRank): ?Rank
    {
        return Rank::where('level', '>', $currentRank->level)
            ->where('is_active', true)
            ->orderBy('level', 'asc')
            ->first();
    }

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

    public function calculateQualifiedBranches(User $user, string $period): array
    {
        $qualifiedBranches = [];

        $directChildren = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->with('rank')
            ->get();

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

    protected function calculateBranchPVOptimized(User $branchRoot): float
    {
        $totalPV = $branchRoot->pv_balance ?? 0;
        $children = User::where('parrain_id', $branchRoot->id)->where('is_active', true)->get();
        foreach ($children as $child) {
            $totalPV += $this->calculateBranchPVOptimized($child);
        }
        return $totalPV;
    }

    public function checkHigherRankEligibility(User $user, string $period): array
    {
        $eligibleRanks = [];
        $higherRanks = HigherRank::where('is_active', true)->orderBy('level', 'asc')->get();

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

    private function isEligibleForHigherRank(User $user, HigherRank $higherRank, string $period): bool
    {
        $level9Branches = $this->countLevel9Branches($user, $period);
        $diamondBranches = $this->countDiamondBranches($user, $period);

        switch ($higherRank->level) {
            case 1: return $level9Branches >= 2;
            case 2: return $level9Branches >= 3;
            case 3: return $level9Branches >= 4;
            case 4: return $level9Branches >= 5;
            case 5: return $level9Branches >= 6;
            case 6: return $level9Branches >= 7;
            case 7: return $level9Branches >= 8;
            case 8: return $diamondBranches >= 4;
            default: return false;
        }
    }

    public function getCurrentHigherRank(User $user): ?HigherRank
    {
        return $user->higherRanks()->orderBy('level', 'desc')->first();
    }

    public function countLevel9Branches(User $user, string $period): int
    {
        return QualifiedBranch::where('user_id', $user->id)
            ->where('period', $period)
            ->where('branch_rank_level', '>=', 9)
            ->count();
    }

    public function countDiamondBranches(User $user, string $period): int
    {
        return QualifiedBranch::where('user_id', $user->id)
            ->where('period', $period)
            ->where('branch_rank_level', '>=', 9)
            ->count();
    }

    public function clearCache(): void
    {
        $this->branchPVCache = [];
        $this->descendantsCache = [];
    }
}