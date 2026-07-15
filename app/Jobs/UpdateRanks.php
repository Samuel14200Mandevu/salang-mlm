<?php

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
use Illuminate\Support\Facades\Cache;

class UpdateRanks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;
    public int $timeout = 3600;
    public int $tries = 3;

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

            $updated = 0;
            $errors = [];

            // ✅ Utilisation de chunk pour éviter les problèmes de mémoire
            $query->chunk(50, function ($users) use ($rankCalculator, &$updated, &$errors) {
                foreach ($users as $user) {
                    try {
                        // ✅ Clear cache pour ce user spécifique
                        Cache::forget("rank_calculated_{$user->id}");

                        $newRank = $rankCalculator->calculateAdvancedRank($user);

                        if ($newRank && $newRank->id != $user->rank_id) {
                            $oldRankId = $user->rank_id;
                            $oldRankName = $user->rank_name;

                            DB::beginTransaction();

                            // ✅ Mise à jour avec gestion des champs
                            $user->rank_id = $newRank->id;
                            $user->rank = $newRank->name;
                            $user->rank_name = $newRank->name;
                            $user->rank_level = $newRank->level;
                            $user->last_rank_update = now();
                            $user->save();

                            // ✅ Enregistrement de l'historique
                            RankHistory::create([
                                'user_id' => $user->id,
                                'old_rank_id' => $oldRankId,
                                'new_rank_id' => $newRank->id,
                                'old_rank_name' => $oldRankName,
                                'new_rank_name' => $newRank->name,
                                'pv_at_time' => $user->pv_balance,
                                'bv_at_time' => $user->bv_balance,
                                'monthly_pv_at_time' => $user->monthly_pv,
                                'notes' => 'Automatic rank update - ' . now()->format('Y-m-d H:i:s'),
                            ]);

                            DB::commit();
                            $updated++;

                            Log::info('User rank updated', [
                                'user_id' => $user->id,
                                'user_name' => $user->name,
                                'old_rank' => $oldRankName,
                                'new_rank' => $newRank->name,
                            ]);

                            // ✅ Vider le cache pour ce user
                            Cache::forget("user_rank_{$user->id}");
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $errors[] = "User ID {$user->id}: " . $e->getMessage();
                        Log::error('Error updating rank for user', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            });

            Log::info('Rank update completed', [
                'period' => now()->format('Y-m'),
                'updated' => $updated,
                'errors' => count($errors),
                'errors_list' => array_slice($errors, 0, 10), // Limiter l'affichage des erreurs
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