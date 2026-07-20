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
                        $this->updateTeamPVRecursive($user);

                        $oldRankId = $user->rank_id;
                        $oldRankName = $user->rank_name ?? 'Distributeur';

                        Cache::forget("rank_calculated_{$user->id}");
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

    private function updateTeamPVRecursive(User $user): void
    {
        $teamPV = $this->calculateTeamPV($user);
        $teamBV = $this->calculateTeamBV($user);
        $totalTeam = $this->countDescendants($user);

        $user->team_pv = $teamPV;
        $user->team_bv = $teamBV;
        $user->total_team = $totalTeam;
        $user->saveQuietly();

        $this->updateAncestorsTeamPV($user);
    }

    private function calculateTeamPV(User $user): int
    {
        $total = $user->pv_balance ?? 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();

        foreach ($children as $child) {
            $total += $this->calculateTeamPV($child);
        }

        return $total;
    }

    private function calculateTeamBV(User $user): int
    {
        $total = $user->bv_balance ?? 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();

        foreach ($children as $child) {
            $total += $this->calculateTeamBV($child);
        }

        return $total;
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
        $current = $user->parrain;
        $level = 1;
        $maxLevel = 20;
        $processed = [];

        while ($current && $level <= $maxLevel && !in_array($current->id, $processed)) {
            $processed[] = $current->id;

            $teamPV = $this->calculateTeamPV($current);
            $teamBV = $this->calculateTeamBV($current);
            $totalTeam = $this->countDescendants($current);

            $current->team_pv = $teamPV;
            $current->team_bv = $teamBV;
            $current->total_team = $totalTeam;
            $current->saveQuietly();

            $current = $current->parrain;
            $level++;
        }
    }
}