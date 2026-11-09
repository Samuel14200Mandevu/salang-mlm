<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\CommissionHistory;
use App\Support\CareerQualificationPrizes;
use App\Support\MlmPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WalletCommissionBreakdown
{
    /**
     * Mois affichés dans Portefeuille → Détail des bonus.
     * À partir d’août de l’année, jusqu’à la période MLM courante
     * (et tout mois suivant déjà présent dans commission_history).
     *
     * @return array<string, string> period => label
     */
    public function bonusMonthOptions(?Carbon $now = null): array
    {
        $now = $now?->copy() ?? now();
        $year = (int) $now->year;

        // Avant août : on reste sur la saison août→… de l’année précédente.
        if ((int) $now->month < 8) {
            $year--;
        }

        $start = sprintf('%04d-08', $year);
        $minEnd = sprintf('%04d-09', $year);
        $current = MlmPeriod::current($now);
        $end = $current >= $start ? $current : $minEnd;

        if ($end < $minEnd) {
            $end = $minEnd;
        }

        $latestHistory = CommissionHistory::query()
            ->where('period', '>=', $start)
            ->where('period', '<=', sprintf('%04d-12', $year))
            ->max('period');

        if (is_string($latestHistory) && $latestHistory > $end) {
            $end = $latestHistory;
        }

        $options = [];
        $cursor = Carbon::createFromFormat('Y-m', $start)->startOfMonth()->locale('fr');
        $endDate = Carbon::createFromFormat('Y-m', $end)->startOfMonth();

        while ($cursor->lte($endDate)) {
            $key = $cursor->format('Y-m');
            $options[$key] = ucfirst($cursor->translatedFormat('F Y'));
            $cursor->addMonth();
        }

        return $options;
    }

    /**
     * Montants par type pour le portefeuille.
     * Priorité : commissions payées visibles ; sinon historique MLM (commission_history) du mois.
     *
     * @return array<string, array{label: string, short: string, total: float, count: int}>
     */
    public function paidByType(int $userId, ?string $period = null): array
    {
        $types = CareerQualificationPrizes::walletBonusTypes();
        $typeKeys = array_keys($types);

        $fromCommissions = $this->aggregateCommissions($userId, $typeKeys, $period);
        $hasCommissionRows = $fromCommissions->sum(fn ($row) => (int) $row->count) > 0;

        $aggregates = $hasCommissionRows
            ? $fromCommissions
            : $this->aggregateHistory($userId, $typeKeys, $period);

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

    /**
     * @param  list<string>  $typeKeys
     * @return \Illuminate\Support\Collection<string, object{type: string, total: mixed, count: mixed}>
     */
    private function aggregateCommissions(int $userId, array $typeKeys, ?string $period)
    {
        $query = Commission::query()
            ->where('user_id', $userId)
            ->visibleToMember()
            ->where('status', 'paid')
            ->whereIn('type', $typeKeys);

        if ($period !== null && $period !== '') {
            $query->where('period', $period);
        }

        return $query
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');
    }

    /**
     * Historique importé quand la table commissions n’a pas encore de lignes payées.
     *
     * @param  list<string>  $typeKeys
     * @return \Illuminate\Support\Collection<string, object{type: string, total: mixed, count: mixed}>
     */
    private function aggregateHistory(int $userId, array $typeKeys, ?string $period)
    {
        $query = CommissionHistory::query()
            ->where('user_id', $userId)
            ->whereIn('type', $typeKeys);

        if ($period !== null && $period !== '') {
            $query->where('period', $period);
        }

        return $query
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');
    }
}
