<?php

namespace App\Services;

use App\Models\Commission;
use App\Support\CareerQualificationPrizes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WalletCommissionBreakdown
{
    /**
     * Mois affichés dans Portefeuille → Détail des bonus (août + septembre).
     *
     * @return array<string, string> period => label
     */
    public function bonusMonthOptions(?Carbon $now = null): array
    {
        $now = $now?->copy() ?? now();
        $year = (int) $now->year;

        // Avant août : on montre encore août/septembre de l’année précédente.
        if ((int) $now->month < 8) {
            $year--;
        }

        $august = Carbon::create($year, 8, 1)->locale('fr');
        $september = Carbon::create($year, 9, 1)->locale('fr');

        return [
            sprintf('%04d-08', $year) => ucfirst($august->translatedFormat('F Y')),
            sprintf('%04d-09', $year) => ucfirst($september->translatedFormat('F Y')),
        ];
    }

    /**
     * Montants payés par type (commissions visibles membre), optionnellement pour un mois MLM.
     *
     * @return array<string, array{label: string, short: string, total: float, count: int}>
     */
    public function paidByType(int $userId, ?string $period = null): array
    {
        $types = CareerQualificationPrizes::walletBonusTypes();
        $query = Commission::query()
            ->where('user_id', $userId)
            ->visibleToMember()
            ->where('status', 'paid')
            ->whereIn('type', array_keys($types));

        if ($period !== null && $period !== '') {
            $query->where('period', $period);
        }

        $aggregates = $query
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
