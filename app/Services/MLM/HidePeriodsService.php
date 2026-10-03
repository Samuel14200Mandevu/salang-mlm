<?php

namespace App\Services\MLM;

use App\Models\CommissionPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HidePeriodsService
{
    public const BAND_C_FROM = '2024-10';

    public const BAND_C_TO = '2026-08';

    /**
     * @return Collection<int, CommissionPeriod>
     */
    public function resolvePeriods(?string $from, ?string $to): Collection
    {
        $from = $from ?: self::BAND_C_FROM;
        $to = $to ?: self::BAND_C_TO;

        return CommissionPeriod::query()
            ->where('period', '>=', $from)
            ->where('period', '<=', $to)
            ->orderBy('period')
            ->get();
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function assertBandCRange(string $from, string $to): array
    {
        if ($from < self::BAND_C_FROM || $to > self::BAND_C_TO) {
            return [
                'ok' => false,
                'message' => sprintf(
                    'Plage hors bande C autorisée (%s → %s). Attendu: %s → %s.',
                    $from,
                    $to,
                    self::BAND_C_FROM,
                    self::BAND_C_TO
                ),
            ];
        }

        if ($from <= '2024-08') {
            return [
                'ok' => false,
                'message' => 'Refus: la plage chevauche la bande A (historique). Utiliser --from=2024-10 minimum.',
            ];
        }

        if ($from <= '2024-09' && $to >= '2024-09') {
            return [
                'ok' => false,
                'message' => 'Refus: la plage inclut la bande B (2024-09). Exclure 2024-09.',
            ];
        }

        if ($from > $to) {
            return ['ok' => false, 'message' => 'Option --from doit être <= --to.'];
        }

        return ['ok' => true, 'message' => ''];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function assertSafeToHide(Collection $periods): array
    {
        if ($periods->isEmpty()) {
            return ['ok' => false, 'message' => 'Aucune commission_period dans la plage.'];
        }

        $historical = $periods->firstWhere('is_historical', true);
        if ($historical) {
            return [
                'ok' => false,
                'message' => "Refus: période {$historical->period} est historique (bande A).",
            ];
        }

        if ($periods->contains('period', '2024-09')) {
            return ['ok' => false, 'message' => 'Refus: 2024-09 (bande B) ne doit pas être masquée.'];
        }

        return ['ok' => true, 'message' => ''];
    }

    /**
     * @return array{periods: list<array<string, mixed>>, totals: array<string, int|float>}
     */
    public function preview(Collection $periods): array
    {
        $rows = [];
        $totals = [
            'periods' => 0,
            'already_hidden' => 0,
            'to_hide' => 0,
            'gross_usd' => 0.0,
        ];

        foreach ($periods as $period) {
            $hidden = (bool) $period->is_hidden;
            $rows[] = [
                'period' => $period->period,
                'status' => $period->status,
                'is_hidden' => $hidden,
                'is_historical' => (bool) $period->is_historical,
                'total_commissions' => (float) $period->total_commissions,
            ];

            $totals['periods']++;
            if ($hidden) {
                $totals['already_hidden']++;
            } else {
                $totals['to_hide']++;
                $totals['gross_usd'] += (float) $period->total_commissions;
            }
        }

        return ['periods' => $rows, 'totals' => $totals];
    }

    /**
     * @return array{periods: list<array<string, mixed>>, totals: array<string, int>}
     */
    public function apply(Collection $periods): array
    {
        $rows = [];
        $updated = 0;

        DB::transaction(function () use ($periods, &$rows, &$updated) {
            foreach ($periods as $period) {
                $wasHidden = (bool) $period->is_hidden;

                if (! $wasHidden) {
                    $period->update([
                        'is_hidden' => true,
                        'is_historical' => false,
                    ]);
                    $updated++;
                }

                $rows[] = [
                    'period' => $period->period,
                    'was_hidden' => $wasHidden,
                    'now_hidden' => true,
                ];
            }
        });

        return [
            'periods' => $rows,
            'totals' => ['periods_updated' => $updated, 'periods_total' => $periods->count()],
        ];
    }
}
