<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\OrderItem;
use App\Models\CommissionPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculatePVBV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $period;
    public int $timeout = 3600;
    public int $tries = 3;

    public function __construct(string $period = null)
    {
        $this->period = $period ?? date('Y-m', strtotime('last month'));
    }

    public function handle(AdvancedRankCalculator $rankCalculator): void
    {
    Log::info('Starting rank update', ['user_id' => $this->userId ?? 'all']);

    try {
        $query = User::where('is_active', true);

        if ($this->userId) {
            $query->where('id', $this->userId);
        }

        $updated = 0;
        $errors = [];

        $query->chunk(50, function ($users) use ($rankCalculator, &$updated, &$errors) {
            foreach ($users as $user) {
                try {
                    Cache::forget("rank_calculated_{$user->id}");

                    $newRank = $rankCalculator->calculateAdvancedRank($user);

                    if ($newRank && $newRank->id != $user->rank_id) {
                        $oldRankId = $user->rank_id;
                        $oldRankName = $user->rank_name;

                        DB::beginTransaction();

                        $user->rank_id = $newRank->id;
                        $user->rank = $newRank->name;
                        $user->rank_name = $newRank->name;
                        $user->rank_level = $newRank->level;
                        $user->last_rank_update = now();
                        
                        // Utiliser saveQuietly() pour éviter les événements
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
        ]);

    } catch (\Exception $e) {
        Log::error('Error updating ranks', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        throw $e;
    }
    }

    /**
     * Met à jour les PV d'équipe pour tous les parrains
     */
    private function updateTeamPV(User $user, int $monthlyPV, int $monthlyBV): void
    {
        $current = $user->parrain;
        $level = 1;
        $maxLevel = 9;

        while ($current && $level <= $maxLevel) {
            $current->team_pv += $monthlyPV;
            $current->team_bv += $monthlyBV;
            $current->save();

            $current = $current->parrain;
            $level++;
        }
    }
    protected function updateCumulativePV(User $user): void
    {
    // Calculer le PV total cumulé
    $totalPV = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('orders.user_id', $user->id)
        ->where('orders.payment_status', 'completed')
        ->sum('order_items.pv_value');

    $totalBV = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('orders.user_id', $user->id)
        ->where('orders.payment_status', 'completed')
        ->sum('order_items.bv_value');

    $user->pv_balance = (int) $totalPV;
    $user->bv_balance = (int) $totalBV;
    $user->save();
    }
}