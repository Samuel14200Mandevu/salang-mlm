<?php

namespace App\Services\MLM;

use App\Models\Commission;
use App\Models\PVHistory;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Gabarit unique du rapport détaillé membre (admin.pv.commission-pdf) —
 * partagé caisse / admin / Mes commissions.
 */
class MemberCommissionDetailedPdfService
{
    /** @return list<string> */
    public function coreTypes(): array
    {
        return ['direct', 'indirect', 'leadership', 'cash_pos'];
    }

    /**
     * @param  Collection<int, Commission>  $commissions
     * @return array{data: array<string, mixed>, filename: string}
     */
    public function build(User $member, Collection $commissions, ?string $period = null): array
    {
        $member->loadMissing(['package', 'parrain:id,name']);

        $bonusOrder = $this->coreTypes();
        $resolvedPeriod = $period && $period !== 'all' ? $period : 'all';

        if ($resolvedPeriod === 'all') {
            $uniquePeriods = $commissions->pluck('period')->filter()->unique();
            if ($uniquePeriods->count() === 1) {
                $resolvedPeriod = (string) $uniquePeriods->first();
            }
        }

        $totals = [
            'direct' => (float) $commissions->where('type', 'direct')->sum('amount'),
            'indirect' => (float) $commissions->where('type', 'indirect')->sum('amount'),
            'leadership' => (float) $commissions->where('type', 'leadership')->sum('amount'),
            'cash_pos' => (float) $commissions->where('type', 'cash_pos')->sum('amount'),
            'total' => (float) $commissions->sum('amount'),
        ];

        $periodPV = ($resolvedPeriod !== 'all')
            ? (float) PVHistory::query()
                ->where('user_id', $member->id)
                ->where('period', $resolvedPeriod)
                ->sum('amount')
            : 0;

        $detailsByType = [];
        $totalsByType = [];
        foreach ($bonusOrder as $type) {
            $detailsByType[$type] = [];
            $totalsByType[$type] = 0.0;
        }

        foreach ($commissions as $item) {
            $type = $item->type;
            if (! isset($detailsByType[$type])) {
                continue;
            }
            $amount = (float) $item->amount;
            $detailsByType[$type][] = [
                'type' => $type,
                'from_user_code' => $item->fromUser?->sponsor_id ?? '—',
                'from_user' => $item->fromUser?->name ?? 'N/A',
                'amount' => $amount,
                'percentage' => $item->percentage,
                'pv_used' => $item->pv_used,
                'generation' => $item->generation,
                'description' => Str::limit((string) ($item->description ?? ''), 45),
            ];
            $totalsByType[$type] += $amount;
        }

        $code = $member->sponsor_id ?: $member->id;
        $periodSlug = $resolvedPeriod === 'all' ? 'toutes' : $resolvedPeriod;

        return [
            'data' => [
                'period' => $resolvedPeriod,
                'commissionsCount' => $commissions->count(),
                'totals' => $totals,
                'beneficiaires' => [[
                    'name' => $member->name,
                    'id' => $member->id,
                    'total' => $totals['total'],
                    'details_by_type' => $detailsByType,
                    'totals_by_type' => $totalsByType,
                ]],
                'user' => $member,
                'userId' => $member->id,
                'periodPV' => $periodPV,
                'date_generated' => now()->format('d/m/Y H:i'),
                'bonusOrder' => $bonusOrder,
                'bonusLabels' => [
                    'direct' => 'Direct Bonus',
                    'indirect' => 'Indirect Bonus',
                    'leadership' => 'Leadership Bonus',
                    'cash_pos' => 'Cash POS Bonus',
                ],
            ],
            'filename' => "rapport_commissions_{$code}_{$periodSlug}.pdf",
        ];
    }
}
