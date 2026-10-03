<?php

namespace App\Services\MLM;

use App\Models\Commission;
use App\Models\CommissionHistory;
use App\Models\CommissionPeriod;
use Illuminate\Support\Collection;
class CommissionHistoryToCommissionsSync
{

    /**
     * @return array{period: string, created: int, skipped: int, errors: int, gross: float}
     */
    public function ensurePeriod(string $periodValue, bool $dryRun = false): array
    {
        $gross = (float) CommissionHistory::query()
            ->where('period', $periodValue)
            ->sum('amount');

        if ($dryRun) {
            return [
                'period' => $periodValue,
                'created' => 0,
                'skipped' => 0,
                'errors' => 0,
                'gross' => $gross,
                'dry_run' => true,
            ];
        }

        $record = CommissionPeriod::findOrCreateForValue($periodValue);
        $record->status = 'calculated';
        $record->total_commissions = $gross;
        $record->save();

        return [
            'period' => $periodValue,
            'created' => 0,
            'skipped' => 0,
            'errors' => 0,
            'gross' => $gross,
            'commission_period_id' => $record->id,
        ];
    }

    /**
     * @return array{created: int, skipped: int, errors: int}
     */
    public function syncHistoryRow(CommissionHistory $history, ?int $commissionPeriodId, bool $dryRun = false): array
    {
        if ($dryRun) {
            $exists = Commission::query()
                ->where('source_commission_history_id', $history->id)
                ->exists();

            return [
                'created' => $exists ? 0 : 1,
                'skipped' => $exists ? 1 : 0,
                'errors' => 0,
            ];
        }

        if ($commissionPeriodId === null) {
            $period = CommissionPeriod::query()->where('period', $history->period)->first();
            $commissionPeriodId = $period?->id;
        }

        if ($commissionPeriodId === null) {
            return ['created' => 0, 'skipped' => 0, 'errors' => 1];
        }

        $description = $history->description;
        if ($description !== null && strlen($description) > 255) {
            $description = substr($description, 0, 252).'...';
        }

        $commission = Commission::query()->firstOrCreate(
            ['source_commission_history_id' => $history->id],
            [
                'user_id' => $history->user_id,
                'from_user_id' => $history->from_user_id,
                'commission_period_id' => $commissionPeriodId,
                'period' => $history->period,
                'type' => $history->type,
                'source' => 'mlm',
                'amount' => $history->amount,
                'percentage' => $history->percentage ?? 0,
                'pv_used' => (int) ($history->pv_used ?? 0),
                'description' => $description,
                'status' => 'pending',
                'calculation_type' => 'manual',
                'generation' => $history->generation ?? 0,
            ]
        );

        if (! $commission->wasRecentlyCreated) {
            return ['created' => 0, 'skipped' => 1, 'errors' => 0];
        }

        return ['created' => 1, 'skipped' => 0, 'errors' => 0];
    }

    /**
     * @return Collection<int, string>
     */
    public function resolvePeriods(?string $from, ?string $to, array $onlyPeriods = []): Collection
    {
        if ($onlyPeriods !== []) {
            return collect($onlyPeriods)->sort()->values();
        }

        $query = CommissionHistory::query()->select('period')->distinct()->orderBy('period');

        if ($from !== null) {
            $query->where('period', '>=', $from);
        }
        if ($to !== null) {
            $query->where('period', '<=', $to);
        }

        return $query->pluck('period');
    }

    /**
     * @return array{periods: array<int, array<string, mixed>>, totals: array<string, int|float>}
     */
    public function syncPeriods(
        Collection $periods,
        bool $dryRun = false,
        bool $ensurePeriods = true,
        int $chunkSize = 500
    ): array {
        $periodReports = [];
        $totals = ['created' => 0, 'skipped' => 0, 'errors' => 0, 'gross' => 0.0];

        foreach ($periods as $periodValue) {
            $periodReport = [
                'period' => $periodValue,
                'created' => 0,
                'skipped' => 0,
                'errors' => 0,
                'gross' => 0.0,
            ];

            if ($ensurePeriods) {
                $ensure = $this->ensurePeriod($periodValue, $dryRun);
                $periodReport['gross'] = $ensure['gross'];
                $commissionPeriodId = $ensure['commission_period_id'] ?? null;
            } else {
                $commissionPeriodId = CommissionPeriod::query()
                    ->where('period', $periodValue)
                    ->value('id');
                $periodReport['gross'] = (float) CommissionHistory::query()
                    ->where('period', $periodValue)
                    ->sum('amount');
            }

            CommissionHistory::query()
                ->where('period', $periodValue)
                ->orderBy('id')
                ->chunkById($chunkSize, function ($rows) use (
                    $dryRun,
                    &$periodReport,
                    $commissionPeriodId
                ) {
                    foreach ($rows as $history) {
                        $result = $this->syncHistoryRow($history, $commissionPeriodId, $dryRun);
                        $periodReport['created'] += $result['created'];
                        $periodReport['skipped'] += $result['skipped'];
                        $periodReport['errors'] += $result['errors'];
                    }
                });

            $totals['created'] += $periodReport['created'];
            $totals['skipped'] += $periodReport['skipped'];
            $totals['errors'] += $periodReport['errors'];
            $totals['gross'] += $periodReport['gross'];
            $periodReports[] = $periodReport;
        }

        return ['periods' => $periodReports, 'totals' => $totals];
    }

    public function checksumForPeriod(string $periodValue): array
    {
        $historySum = (float) CommissionHistory::query()->where('period', $periodValue)->sum('amount');
        $historyCount = CommissionHistory::query()->where('period', $periodValue)->count();

        $commissionQuery = Commission::query()
            ->where('period', $periodValue)
            ->whereNotNull('source_commission_history_id');

        return [
            'period' => $periodValue,
            'history_count' => $historyCount,
            'history_sum' => $historySum,
            'commission_synced_count' => (clone $commissionQuery)->count(),
            'commission_synced_sum' => (float) (clone $commissionQuery)->sum('amount'),
            'aligned' => abs($historySum - (float) (clone $commissionQuery)->sum('amount')) < 0.01
                && $historyCount === (clone $commissionQuery)->count(),
        ];
    }
}
