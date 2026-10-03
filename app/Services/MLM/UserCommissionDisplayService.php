<?php

namespace App\Services\MLM;

use App\Models\Commission;
use Illuminate\Database\Eloquent\Builder;

class UserCommissionDisplayService
{
    public function visibleQuery(int $userId): Builder
    {
        return Commission::query()
            ->where('user_id', $userId)
            ->visibleToMember();
    }

    /**
     * Agrégats membre (bandes A / B / C).
     *
     * @return array<string, float|int>
     */
    public function summarize(int $userId): array
    {
        $paidOfflineHistorical = (float) Commission::query()
            ->where('user_id', $userId)
            ->paid()
            ->whereHas('period', fn ($q) => $q->where('is_historical', true))
            ->sum('amount');

        $paidSystem = (float) Commission::query()
            ->where('user_id', $userId)
            ->paid()
            ->whereHas('period', fn ($q) => $q->where('is_historical', false)->where('is_hidden', false))
            ->sum('amount');

        $paidTotalVisible = $paidOfflineHistorical + $paidSystem;

        $pendingPayable = (float) Commission::query()
            ->where('user_id', $userId)
            ->pendingPayableViaSystem()
            ->sum('amount');

        $pendingPayableCount = (int) Commission::query()
            ->where('user_id', $userId)
            ->pendingPayableViaSystem()
            ->count();

        $visibleBase = $this->visibleQuery($userId);

        $totalVisible = (float) (clone $visibleBase)->sum('amount');
        $totalVisibleCount = (int) (clone $visibleBase)->count();

        $paidVisibleCount = (int) (clone $visibleBase)->paid()->count();

        return [
            'paid_offline_historical' => $paidOfflineHistorical,
            'paid_system' => $paidSystem,
            'paid_total_visible' => $paidTotalVisible,
            'pending_payable' => $pendingPayable,
            'pending_payable_count' => $pendingPayableCount,
            'total_visible' => $totalVisible,
            'total_visible_count' => $totalVisibleCount,
            'paid_visible_count' => $paidVisibleCount,
        ];
    }

    /**
     * Stats legacy + bandes pour API / vues.
     *
     * @return array<string, mixed>
     */
    public function memberStatsPayload(int $userId): array
    {
        $bands = $this->summarize($userId);

        return array_merge($bands, [
            'total_commission' => $bands['paid_total_visible'],
            'pending_commission' => $bands['pending_payable'],
            'paid_commission' => $bands['paid_total_visible'],
            'bands' => [
                'historical_paid' => $bands['paid_offline_historical'],
                'system_paid' => $bands['paid_system'],
                'payable_pending' => $bands['pending_payable'],
            ],
        ]);
    }
}
