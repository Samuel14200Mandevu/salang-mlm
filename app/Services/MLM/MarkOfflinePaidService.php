<?php

namespace App\Services\MLM;

use App\Models\Commission;
use App\Models\CommissionHistory;
use App\Models\CommissionPayment;
use App\Models\CommissionPeriod;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MarkOfflinePaidService
{
    public const BAND_A_FROM = '2023-11';

    public const BAND_A_TO = '2024-08';

    /**
     * @return Collection<int, CommissionPeriod>
     */
    public function resolvePeriods(?string $from, ?string $to): Collection
    {
        $from = $from ?: self::BAND_A_FROM;
        $to = $to ?: self::BAND_A_TO;

        return CommissionPeriod::query()
            ->where('period', '>=', $from)
            ->where('period', '<=', $to)
            ->orderBy('period')
            ->get();
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function assertBandARange(string $from, string $to): array
    {
        if ($from < self::BAND_A_FROM || $to > self::BAND_A_TO) {
            return [
                'ok' => false,
                'message' => sprintf(
                    'Plage hors bande A autorisée (%s → %s). Max: %s → %s.',
                    $from,
                    $to,
                    self::BAND_A_FROM,
                    self::BAND_A_TO
                ),
            ];
        }

        if ($from > $to) {
            return ['ok' => false, 'message' => 'Option --from doit être <= --to.'];
        }

        return ['ok' => true, 'message' => ''];
    }

    /**
     * @return array{ok: bool, message: string, payments?: int, transactions?: int}
     */
    public function assertSafeToMark(Collection $periods): array
    {
        if ($periods->isEmpty()) {
            return ['ok' => false, 'message' => 'Aucune commission_period dans la plage.'];
        }

        $periodIds = $periods->pluck('id');

        $payments = CommissionPayment::query()
            ->whereIn('commission_period_id', $periodIds)
            ->count();

        if ($payments > 0) {
            return [
                'ok' => false,
                'message' => "Refus: {$payments} commission_payments existent pour ces périodes.",
                'payments' => $payments,
            ];
        }

        $periodValues = $periods->pluck('period')->all();

        $transactions = Transaction::query()
            ->where('type', 'commission')
            ->where(function ($q) use ($periodValues) {
                foreach ($periodValues as $period) {
                    $q->orWhere('reference', 'like', 'COMM-'.$period.'-%');
                }
            })
            ->count();

        if ($transactions > 0) {
            return [
                'ok' => false,
                'message' => "Refus: {$transactions} transactions commission existent pour ces périodes.",
                'transactions' => $transactions,
            ];
        }

        return ['ok' => true, 'message' => ''];
    }

    /**
     * @return array{periods: list<array<string, mixed>>, totals: array<string, float|int>}
     */
    public function preview(Collection $periods): array
    {
        $rows = [];
        $totals = [
            'periods' => 0,
            'commissions_pending' => 0,
            'history_pending' => 0,
            'gross_usd' => 0.0,
        ];

        foreach ($periods as $period) {
            $pendingCommissions = Commission::query()
                ->where('commission_period_id', $period->id)
                ->where('status', 'pending');

            $pendingCount = (clone $pendingCommissions)->count();
            $gross = (float) (clone $pendingCommissions)->sum('amount');

            $historyPending = CommissionHistory::query()
                ->where('period', $period->period)
                ->where('status', 'pending')
                ->count();

            $rows[] = [
                'period' => $period->period,
                'status' => $period->status,
                'is_historical' => (bool) $period->is_historical,
                'commissions_pending' => $pendingCount,
                'history_pending' => $historyPending,
                'gross_usd' => $gross,
            ];

            $totals['periods']++;
            $totals['commissions_pending'] += $pendingCount;
            $totals['history_pending'] += $historyPending;
            $totals['gross_usd'] += $gross;
        }

        return ['periods' => $rows, 'totals' => $totals];
    }

    /**
     * @return array{periods: list<array<string, mixed>>, totals: array<string, int|float>}
     */
    public function apply(Collection $periods, Carbon $paidAt): array
    {
        $rows = [];
        $totals = [
            'periods_updated' => 0,
            'commissions_updated' => 0,
            'history_updated' => 0,
            'gross_usd' => 0.0,
        ];

        DB::transaction(function () use ($periods, $paidAt, &$rows, &$totals) {
            foreach ($periods as $period) {
                $pendingCommissions = Commission::query()
                    ->where('commission_period_id', $period->id)
                    ->where('status', 'pending');

                $gross = (float) (clone $pendingCommissions)->sum('amount');
                $commUpdated = $pendingCommissions->update([
                    'status' => 'paid',
                    'paid_at' => $paidAt,
                ]);

                $histUpdated = CommissionHistory::query()
                    ->where('period', $period->period)
                    ->where('status', 'pending')
                    ->update(['status' => 'paid']);

                $noteSuffix = sprintf(
                    ' | Marquage historique offline (bande A). Montant de référence: %s USD. Aucun crédit wallet.',
                    number_format((float) $period->total_commissions, 2, '.', '')
                );
                $notes = trim((string) $period->notes);
                if ($notes !== '' && ! str_contains($notes, 'Marquage historique offline')) {
                    $notes .= $noteSuffix;
                } elseif ($notes === '') {
                    $notes = ltrim($noteSuffix, ' |');
                }

                $period->update([
                    'is_historical' => true,
                    'is_hidden' => false,
                    'paid_offline_at' => $paidAt,
                    'status' => 'paid',
                    'payment_date' => $paidAt,
                    'total_paid' => 0,
                    'notes' => $notes,
                ]);

                $rows[] = [
                    'period' => $period->period,
                    'commissions_updated' => $commUpdated,
                    'history_updated' => $histUpdated,
                    'gross_usd' => $gross,
                ];

                $totals['periods_updated']++;
                $totals['commissions_updated'] += $commUpdated;
                $totals['history_updated'] += $histUpdated;
                $totals['gross_usd'] += $gross;
            }
        });

        return ['periods' => $rows, 'totals' => $totals];
    }
}
