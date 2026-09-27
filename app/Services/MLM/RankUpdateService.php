<?php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\RankHistory;
use App\Jobs\RecalculateAfterPVImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * ══════════════════════════════════════════════════════════════════════
 * RankUpdateService - VERSION UNIFIÉE
 * ══════════════════════════════════════════════════════════════════════
 *
 * Utilise TeamPVCalculator + AdvancedRankCalculator.
 * Ne duplique AUCUNE formule.
 * Toutes les recalculations passent par RecalculateAfterPVImport.
 */
class RankUpdateService
{
    protected AdvancedRankCalculator $rankCalculator;
    protected TeamPVCalculator $teamPVCalculator;

    public function __construct(
        AdvancedRankCalculator $rankCalculator,
        TeamPVCalculator $teamPVCalculator
    ) {
        $this->rankCalculator = $rankCalculator;
        $this->teamPVCalculator = $teamPVCalculator;
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
            // 1. Mise à jour team_pv via service unique
            $this->teamPVCalculator->updateUser($user);

            // 2. Mise à jour du grade
            $rankChanged = $this->updateRankSync($user);

            // 3. Propagation aux ancêtres si changement
            if ($rankChanged) {
                $this->teamPVCalculator->updateAncestors($user);
            }

            $this->clearCache($user);

            Log::info('Rank update triggered', [
                'user_id' => $user->id,
                'rank_changed' => $rankChanged,
                'reason' => $reason,
                'current_rank' => $user->rank,
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
        try {
            $oldRankId = $user->rank_id;
            $oldRankName = $user->rank ?? 'Distributeur';
            $oldRankLevel = $user->rank_level ?? 1;

            $newRank = $this->rankCalculator->calculateAdvancedRank($user);

            if (!$newRank) {
                return false;
            }

            $needsUpdate = (
                $newRank->id != $user->rank_id ||
                $newRank->name != $user->rank ||
                $newRank->level != $user->rank_level
            );

            if (!$needsUpdate) {
                return false;
            }

            DB::beginTransaction();

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
                    'old_rank_level' => $oldRankLevel,
                    'new_rank_name' => $newRank->name,
                    'new_rank_level' => $newRank->level,
                    'pv_at_time' => $user->pv_balance ?? 0,
                    'bv_at_time' => $user->bv_balance ?? 0,
                    'notes' => 'Rank updated',
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

    public function triggerRankUpdateAsync(User $user, string $reason = 'bulk_import'): void
    {
        RecalculateAfterPVImport::dispatch([$user->id], date('Y-m'))
            ->onQueue('rank-recalculation');

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
        $this->teamPVCalculator->clearCache($user);
    }
}