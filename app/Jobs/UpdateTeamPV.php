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

                    // ✅ Mettre à jour le team_pv avec CTE
                    $this->updateTeamPVWithCTE($user);

                    if ($this->recursive) {
                        // ✅ Mettre à jour TOUS les ancêtres avec CTE
                        $this->updateAncestorsTeamPVWithCTE($user);
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

    /**
     * ✅ Met à jour le team_pv avec CTE (1 requête)
     */
    private function updateTeamPVWithCTE(User $user): void
    {
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