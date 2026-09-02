<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\OrderItem;
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

class CalculatePVBV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;
    protected string $period;
    public int $timeout = 3600;
    public int $tries = 3;

    public function __construct(?int $userId = null, string $period = null)
    {
        $this->userId = $userId;
        $this->period = $period ?? date('Y-m');
    }

    public function handle(AdvancedRankCalculator $rankCalculator): void
    {
        Log::info('CalculatePVBV started', [
            'user_id' => $this->userId ?? 'all',
            'period' => $this->period
        ]);

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
                        DB::beginTransaction();

                        $this->updateCumulativePV($user);
                        $this->updateMonthlyPV($user);

                        // ✅ Utiliser CTE pour le team_pv
                        $this->updateTeamPVWithCTE($user);

                        $oldRankId = $user->rank_id;
                        $oldRankName = $user->rank_name ?? 'Distributeur';

                        Cache::forget("rank_calculation_{$user->id}");
                        Cache::forget("user_rank_{$user->id}");

                        $newRank = $rankCalculator->calculateAdvancedRank($user);

                        if ($newRank && $newRank->id != $oldRankId) {
                            $user->rank_id = $newRank->id;
                            $user->rank = $newRank->name;
                            $user->rank_name = $newRank->name;
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
                                'notes' => 'Calcul PVBV - ' . $this->period,
                            ]);

                            $updated++;

                            Log::info('Rank updated via CalculatePVBV', [
                                'user_id' => $user->id,
                                'user_name' => $user->name,
                                'old_rank' => $oldRankName,
                                'new_rank' => $newRank->name,
                            ]);

                            Cache::forget("user_rank_{$user->id}");
                        }

                        // ✅ Mettre à jour TOUS les ancêtres avec CTE
                        $this->updateAncestorsTeamPVWithCTE($user);

                        DB::commit();

                    } catch (\Exception $e) {
                        DB::rollBack();
                        $errors[] = "User ID {$user->id}: " . $e->getMessage();
                        Log::error('Error in CalculatePVBV', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            });

            Log::info('CalculatePVBV completed', [
                'period' => $this->period,
                'updated' => $updated,
                'errors' => count($errors),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in CalculatePVBV', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function updateCumulativePV(User $user): void
    {
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
        $user->saveQuietly();
    }

    private function updateMonthlyPV(User $user): void
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $activatedThisMonth = false;
        if ($user->activated_at) {
            $activatedThisMonth = $user->activated_at->between($monthStart, $monthEnd);
        }

        if ($activatedThisMonth) {
            $user->monthly_pv = $user->pv_balance;
            $user->monthly_bv = $user->bv_balance;
        } else {
            $totalPV = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.user_id', $user->id)
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->where('orders.payment_status', 'completed')
                ->sum('order_items.pv_value');

            $totalBV = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.user_id', $user->id)
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->where('orders.payment_status', 'completed')
                ->sum('order_items.bv_value');

            $user->monthly_pv = (int) $totalPV;
            $user->monthly_bv = (int) $totalBV;
        }

        $user->saveQuietly();
    }

    /**
     * ✅ Met à jour le team_pv avec CTE (1 requête)
     */
    private function updateTeamPVWithCTE(User $user): void
    {
        // Récupérer TOUS les descendants en 1 requête
        $result = DB::select("
            WITH RECURSIVE descendants AS (
                SELECT id, pv_balance, bv_balance, 1 as depth
                FROM users 
                WHERE id = ?
                
                UNION ALL
                
                SELECT u.id, u.pv_balance, u.bv_balance, d.depth + 1
                FROM users u
                INNER JOIN descendants d ON u.parrain_id = d.id
                WHERE u.is_active = true
            )
            SELECT 
                COALESCE(SUM(pv_balance), 0) as total_pv,
                COALESCE(SUM(bv_balance), 0) as total_bv,
                COUNT(*) as total_members
            FROM descendants
        ", [$user->id]);

        if ($result && isset($result[0])) {
            $user->team_pv = $result[0]->total_pv ?? 0;
            $user->team_bv = $result[0]->total_bv ?? 0;
            $user->total_team = ($result[0]->total_members ?? 1) - 1;
            $user->saveQuietly();
        }
    }

    /**
     * ✅ Met à jour TOUS les ancêtres avec CTE (1 requête)
     */
    private function updateAncestorsTeamPVWithCTE(User $user): void
    {
        // Récupérer TOUS les ancêtres en 1 requête
        $ancestorIds = DB::select("
            WITH RECURSIVE ancestors AS (
                SELECT id, parrain_id, 1 as level
                FROM users 
                WHERE id = ?
                
                UNION ALL
                
                SELECT u.id, u.parrain_id, a.level + 1
                FROM users u
                INNER JOIN ancestors a ON u.id = a.parrain_id
                WHERE u.is_active = true
            )
            SELECT id, level FROM ancestors ORDER BY level DESC
        ", [$user->id]);

        if (empty($ancestorIds)) {
            return;
        }

        $ids = array_column($ancestorIds, 'id');

        // Mettre à jour le team_pv de TOUS les ancêtres en 1 requête
        DB::statement("
            UPDATE users u
            SET team_pv = (
                SELECT COALESCE(SUM(pv_balance + monthly_pv + team_pv), 0)
                FROM users
                WHERE parrain_id = u.id
                AND is_active = true
            ),
            team_bv = (
                SELECT COALESCE(SUM(bv_balance + monthly_bv + team_bv), 0)
                FROM users
                WHERE parrain_id = u.id
                AND is_active = true
            ),
            total_team = (
                SELECT COUNT(*)
                FROM users
                WHERE parrain_id = u.id
                AND is_active = true
            )
            WHERE u.id IN (" . implode(',', $ids) . ")
        ");
    }
}