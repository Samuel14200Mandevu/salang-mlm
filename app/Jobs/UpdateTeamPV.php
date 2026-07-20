<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UpdateTeamPV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;
    protected bool $recursive;
    public int $timeout = 3600;
    public int $tries = 3;

    public function __construct(?int $userId = null, bool $recursive = true)
    {
        $this->userId = $userId;
        $this->recursive = $recursive;
    }

    public function handle(): void
    {
        Log::info('UpdateTeamPV started', [
            'user_id' => $this->userId ?? 'all',
            'recursive' => $this->recursive
        ]);

        $query = User::where('is_active', true);

        if ($this->userId) {
            $query->where('id', $this->userId);
        }

        $query->chunk(100, function ($users) {
            foreach ($users as $user) {
                try {
                    DB::beginTransaction();

                    $this->calculateAndUpdateTeamPV($user);

                    if ($this->recursive) {
                        $this->updateAncestorsTeamPV($user);
                    }

                    Cache::forget("descendants_{$user->id}");
                    Cache::forget("descendants_count_{$user->id}");
                    Cache::forget("user_rank_{$user->id}");

                    $user->calculateAndUpdateRank();

                    DB::commit();

                    Log::debug('TeamPV updated', [
                        'user_id' => $user->id,
                        'team_pv' => $user->team_pv,
                        'rank' => $user->rank_name,
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('UpdateTeamPV error', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        Log::info('UpdateTeamPV completed');
    }

    private function calculateAndUpdateTeamPV(User $user): void
    {
        $teamPV = $this->calculateTeamPVRecursive($user);
        $teamBV = $this->calculateTeamBVRecursive($user);
        $totalTeam = $this->countDescendants($user);

        $user->team_pv = $teamPV;
        $user->team_bv = $teamBV;
        $user->total_team = $totalTeam;
        $user->saveQuietly();

        Cache::forget("descendants_{$user->id}");
        Cache::forget("descendants_count_{$user->id}");
    }

    private function calculateTeamPVRecursive(User $user): int
    {
        $totalPV = $user->pv_balance ?? 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();

        foreach ($children as $child) {
            $totalPV += $this->calculateTeamPVRecursive($child);
        }

        return $totalPV;
    }

    private function calculateTeamBVRecursive(User $user): int
    {
        $totalBV = $user->bv_balance ?? 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();

        foreach ($children as $child) {
            $totalBV += $this->calculateTeamBVRecursive($child);
        }

        return $totalBV;
    }

    private function countDescendants(User $user): int
    {
        $count = 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();

        foreach ($children as $child) {
            $count++;
            $count += $this->countDescendants($child);
        }

        return $count;
    }

    private function updateAncestorsTeamPV(User $user): void
    {
        $lockKey = "ancestor_update_{$user->id}";

        if (Cache::get($lockKey, false)) {
            return;
        }

        Cache::put($lockKey, true, 60);

        try {
            $current = $user->parrain;
            $level = 1;
            $maxLevel = 20;
            $processed = [];

            while ($current && $level <= $maxLevel) {
                if (in_array($current->id, $processed)) {
                    break;
                }

                $processed[] = $current->id;
                $this->calculateAndUpdateTeamPV($current);

                Cache::forget("descendants_{$current->id}");
                Cache::forget("user_rank_{$current->id}");

                $current->calculateAndUpdateRank();

                Log::debug('Ancestor TeamPV updated', [
                    'ancestor_id' => $current->id,
                    'ancestor_name' => $current->name,
                    'team_pv' => $current->team_pv,
                    'rank' => $current->rank_name,
                ]);

                $current = $current->parrain;
                $level++;
            }
        } finally {
            Cache::forget($lockKey);
        }
    }
}