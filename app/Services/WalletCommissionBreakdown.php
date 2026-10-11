<?php

namespace App\Services;

use App\Models\Commission;
use App\Support\CareerQualificationPrizes;
use Illuminate\Support\Facades\DB;

class WalletCommissionBreakdown
{
    /**
     * Montants payés par type (commissions visibles membre).
     *
     * @return array<string, array{label: string, short: string, total: float, count: int}>
     */
    public function paidByType(int $userId): array
    {
        $types = CareerQualificationPrizes::walletBonusTypes();
        $aggregates = Commission::query()
            ->where('user_id', $userId)
            ->visibleToMember()
            ->where('status', 'paid')
            ->whereIn('type', array_keys($types))
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $result = [];
        foreach ($types as $key => $meta) {
            $row = $aggregates->get($key);
            $result[$key] = [
                'label' => $meta['label'],
                'short' => $meta['short'],
                'total' => $row ? (float) $row->total : 0.0,
                'count' => $row ? (int) $row->count : 0,
            ];
        }

        return $result;
    }
}
