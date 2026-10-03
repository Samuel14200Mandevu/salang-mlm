<?php

namespace App\Services\MLM;

use App\Models\Commission;
use App\Models\CommissionHistory;
use Illuminate\Support\Facades\Log;

class CommissionHistoryPaymentSync
{
    /**
     * D3 — Aligne commission_history sur commissions payées pour un user/période.
     */
    public function markPaidForUserPeriod(int $userId, string $periodValue): int
    {
        $historyIds = Commission::query()
            ->where('user_id', $userId)
            ->where('period', $periodValue)
            ->where('status', 'paid')
            ->whereNotNull('source_commission_history_id')
            ->pluck('source_commission_history_id');

        if ($historyIds->isEmpty()) {
            return CommissionHistory::query()
                ->where('user_id', $userId)
                ->where('period', $periodValue)
                ->where('status', 'pending')
                ->update(['status' => 'paid']);
        }

        $updated = CommissionHistory::query()
            ->whereIn('id', $historyIds)
            ->where('status', '!=', 'paid')
            ->update(['status' => 'paid']);

        Log::debug('commission_history marked paid after payment', [
            'user_id' => $userId,
            'period' => $periodValue,
            'updated' => $updated,
        ]);

        return $updated;
    }
}
