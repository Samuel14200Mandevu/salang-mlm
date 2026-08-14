<?php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\RankHistory;
use App\Jobs\UpdateRanks;
use App\Jobs\UpdateTeamPV;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RankUpdateService
{
    protected AdvancedRankCalculator $rankCalculator;
    protected int $maxDepth = 5;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        $this->rankCalculator = $rankCalculator;
    }

    public function triggerRankUpdate(User $user, string $reason = 'pv_update'): void
    {
        $lockKey = "rank_update_lock_{$user->id}";
        
        if (Cache::get($lockKey, false)) {
            Log::debug('Rank update already in progress', ['user_id' => $user->id]);
            return;
        }

        Cache::put($lockKey, true, 30);

        try {
            $this->updateTeamPVOptimized($user);
            $rankChanged = $this->updateRankSync($user);

            if ($rankChanged) {
                $this->updateAncestors($user, 'rank_changed');
            }

            $this->clearCache($user);

            Log::info('Rank update triggered', [
                'user_id' => $user->id,
                'rank_changed' => $rankChanged,
                'reason' => $reason,
                'current_rank' => $user->rank,
                'current_level' => $user->rank_level,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in triggerRankUpdate', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            Cache::forget($lockKey);
        }
    }

    protected function updateRankSync(User $user): bool
    {
        $lastUpdate = $user->last_rank_update;
        if ($lastUpdate && $lastUpdate instanceof \Carbon\Carbon) {
            if ($lastUpdate->diffInMinutes(now()) < 1) {
                return false;
            }
        }

        $oldRankId = $user->rank_id;
        $oldRankName = $user->rank ?? 'Distributeur';

        $newRank = $this->rankCalculator->calculateAdvancedRank($user);

        if (!$newRank || $newRank->id == $oldRankId) {
            return false;
        }

        DB::beginTransaction();
        try {
            $user->rank_id = $newRank->id;
            $user->rank = $newRank->name;
            $user->rank_level = $newRank->level;
            $user->last_rank_update = now();
            $user->rank_update_queued = 0;
            $user->saveQuietly();

            try {
                RankHistory::create([
                    'user_id' => $user->id,
                    'old_rank_id' => $oldRankId,
                    'new_rank_id' => $newRank->id,
                    'old_rank_name' => $oldRankName,
                    'old_rank_level' => $user->getOriginal('rank_level') ?? 1,
                    'new_rank_name' => $newRank->name,
                    'new_rank_level' => $newRank->level,
                    'pv_at_time' => $user->pv_balance ?? 0,
                    'bv_at_time' => $user->bv_balance ?? 0,
                ]);
            } catch (\Exception $e) {
                Log::warning('Could not save rank history', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            Log::info('Rank updated', [
                'user_id' => $user->id,
                'old_rank' => $oldRankName,
                'new_rank' => $newRank->name,
                'new_level' => $newRank->level,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating rank', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected function updateTeamPVOptimized(User $user): void
    {
        try {
            $result = DB::select("
                WITH RECURSIVE team AS (
                    SELECT id, pv_balance, bv_balance, 1 as depth
                    FROM users 
                    WHERE id = ?
                    
                    UNION ALL
                    
                    SELECT u.id, u.pv_balance, u.bv_balance, t.depth + 1
                    FROM users u
                    INNER JOIN team t ON u.parrain_id = t.id
                    WHERE u.is_active = 1 
                    AND t.depth < ?
                )
                SELECT 
                    COALESCE(SUM(pv_balance), 0) as total_pv,
                    COALESCE(SUM(bv_balance), 0) as total_bv,
                    COUNT(*) as total_members
                FROM team
            ", [$user->id, $this->maxDepth]);

            if ($result && isset($result[0])) {
                $user->team_pv = $result[0]->total_pv ?? 0;
                $user->team_bv = $result[0]->total_bv ?? 0;
                $user->total_team = ($result[0]->total_members ?? 1) - 1;
                $user->saveQuietly();
            }

        } catch (\Exception $e) {
            Log::warning('CTE team_pv failed, using fallback', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            $this->updateTeamPVFallback($user);
        }
    }

    protected function updateTeamPVFallback(User $user): void
    {
        $totalPV = $user->pv_balance ?? 0;
        $totalBV = $user->bv_balance ?? 0;
        $totalCount = 0;

        $stack = [$user->id];
        $processed = [];

        while (!empty($stack)) {
            $currentId = array_pop($stack);
            
            if (in_array($currentId, $processed)) {
                continue;
            }
            
            $processed[] = $currentId;
            
            $children = User::where('parrain_id', $currentId)
                ->where('is_active', true)
                ->select('id', 'pv_balance', 'bv_balance')
                ->get();

            foreach ($children as $child) {
                $totalPV += $child->pv_balance ?? 0;
                $totalBV += $child->bv_balance ?? 0;
                $totalCount++;
                $stack[] = $child->id;
            }
        }

        $user->team_pv = $totalPV;
        $user->team_bv = $totalBV;
        $user->total_team = $totalCount;
        $user->saveQuietly();
    }

    protected function updateAncestors(User $user, string $reason = 'pv_changed'): void
    {
        $ancestor = $user->parrain;
        $level = 1;

        while ($ancestor && $level <= $this->maxDepth) {
            $this->updateTeamPVOptimized($ancestor);
            $this->updateRankSync($ancestor);
            $this->clearCache($ancestor);
            
            $ancestor = $ancestor->parrain;
            $level++;
        }
    }

    public function triggerRankUpdateAsync(User $user, string $reason = 'bulk_import'): void
    {
        $this->updateTeamPVOptimized($user);

        dispatch(new UpdateRanks($user->id))->onQueue('high');
        dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');

        if ($user->parrain_id) {
            dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
            dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
        }

        $this->clearCache($user);

        Log::info('Rank update dispatched async', [
            'user_id' => $user->id,
            'reason' => $reason,
        ]);
    }

    protected function clearCache(User $user): void
    {
        Cache::forget("user_rank_{$user->id}");
        Cache::forget("rank_calculation_{$user->id}");
        Cache::forget("descendants_{$user->id}");
        Cache::forget("descendants_count_{$user->id}");
        $this->rankCalculator->clearCache();
    }
}