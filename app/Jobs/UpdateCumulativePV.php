<?php
// app/Jobs/UpdateCumulativePV.php

namespace App\Jobs;

use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // ✅ Ajouté

class UpdateCumulativePV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;
    public int $timeout = 3600;
    public int $tries = 3;

    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        Log::info('Starting cumulative PV update', [
            'user_id' => $this->userId ?? 'all'
        ]);

        try {
            $query = User::where('is_active', true);

            if ($this->userId) {
                $query->where('id', $this->userId);
            }

            $updated = 0;
            $errors = [];

            $query->chunk(100, function ($users) use (&$updated, &$errors) {
                foreach ($users as $user) {
                    try {
                        DB::beginTransaction();

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

                        // ✅ Utiliser CTE pour mettre à jour les ancêtres
                        $this->updateAncestorsTeamPVWithCTE($user);

                        DB::commit();
                        $updated++;

                    } catch (\Exception $e) {
                        DB::rollBack();
                        $errors[] = "User {$user->id}: " . $e->getMessage();
                        Log::error('Error updating cumulative PV', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

            Log::info('Cumulative PV update completed', [
                'users_updated' => $updated,
                'errors' => count($errors)
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating cumulative PV', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * ✅ Met à jour TOUS les ancêtres avec CTE (1 requête)
     */
    private function updateAncestorsTeamPVWithCTE(User $user): void
    {
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