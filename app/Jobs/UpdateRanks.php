<?php
// app/Jobs/UpdateRanks.php

namespace App\Jobs;

use App\Models\User;
use App\Models\RankHistory;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateRanks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;
    public int $timeout = 3600;

    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
    }

    public function handle(AdvancedRankCalculator $rankCalculator): void
    {
        Log::info('Starting rank update', [
            'user_id' => $this->userId ?? 'all',
        ]);

        try {
            $query = User::where('is_active', true);

            if ($this->userId) {
                $query->where('id', $this->userId);
            }

            $users = $query->get();
            $updated = 0;
            $errors = [];

            foreach ($users as $user) {
                try {
                    $newRank = $rankCalculator->calculateAdvancedRank($user);

                    if ($newRank && $newRank->id != $user->rank_id) {
                        $oldRankId = $user->rank_id;
                        $oldRankName = $user->rank_name;

                        DB::beginTransaction();

                        $user->rank_id = $newRank->id;
                        $user->rank = $newRank->name;
                        $user->last_rank_update = now();
                        $user->save();

                        RankHistory::create([
                            'user_id' => $user->id,
                            'old_rank_id' => $oldRankId,
                            'new_rank_id' => $newRank->id,
                            'old_rank_name' => $oldRankName,
                            'new_rank_name' => $newRank->name,
                            'pv_at_time' => $user->pv_balance,
                            'bv_at_time' => $user->bv_balance,
                            'notes' => 'Automatic rank update',
                        ]);

                        DB::commit();
                        $updated++;

                        Log::info('User rank updated', [
                            'user_id' => $user->id,
                            'old_rank' => $oldRankName,
                            'new_rank' => $newRank->name,
                        ]);
                    }
                } catch (\Exception $e) {
                    $errors[] = "User ID {$user->id}: " . $e->getMessage();
                    DB::rollBack();
                }
            }

            Log::info('Rank update completed', [
                'updated' => $updated,
                'errors' => count($errors),
                'errors_list' => $errors,
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating ranks', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}