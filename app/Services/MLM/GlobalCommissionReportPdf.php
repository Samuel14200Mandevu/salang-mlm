<?php

namespace App\Services\MLM;

use App\Models\Commission;
use App\Models\CommissionHistory;
use App\Models\PVHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Données partagées du Rapport Global (admin + caissier) —
 * même agrégation pour aperçu inline et téléchargement attachment.
 */
class GlobalCommissionReportPdf
{
    /** @var list<string> */
    public const CORE_TYPES = ['direct', 'indirect', 'leadership', 'cash_pos'];

    /**
     * @param  Collection<int, object>  $commissions  Lignes avec user + parrain chargés
     * @return array{
     *   commissionsCount:int,
     *   totals:array<string,float>,
     *   typeStats:array<string,array{count:int,total:float,percentage:float,average:float}>,
     *   beneficiaires:Collection<int,array<string,mixed>>
     * }
     */
    public function buildFromRows(Collection $commissions, string $period): array
    {
        $rows = $commissions
            ->filter(fn ($c) => in_array((string) $c->type, self::CORE_TYPES, true))
            ->values();

        $totals = [
            'direct' => (float) $rows->where('type', 'direct')->sum('amount'),
            'indirect' => (float) $rows->where('type', 'indirect')->sum('amount'),
            'leadership' => (float) $rows->where('type', 'leadership')->sum('amount'),
            'cash_pos' => (float) $rows->where('type', 'cash_pos')->sum('amount'),
            'total' => 0.0,
        ];
        $totals['total'] = round(
            $totals['direct'] + $totals['indirect'] + $totals['leadership'] + $totals['cash_pos'],
            2
        );

        $commissionsCount = $rows->count();

        $typeStats = [];
        foreach (self::CORE_TYPES as $type) {
            $typeItems = $rows->where('type', $type);
            $count = $typeItems->count();
            $total = (float) $typeItems->sum('amount');
            $typeStats[$type] = [
                'count' => $count,
                'total' => $total,
                'percentage' => $totals['total'] > 0 ? ($total / $totals['total']) * 100 : 0,
                'average' => $count > 0 ? $total / $count : 0,
            ];
        }

        $userIds = $rows->pluck('user_id')->unique()->filter()->values()->all();
        $pvByUser = [];
        if ($period !== 'all' && $userIds !== []) {
            $pvByUser = PVHistory::query()
                ->whereIn('user_id', $userIds)
                ->where('period', $period)
                ->select('user_id', DB::raw('SUM(amount) as total_pv'))
                ->groupBy('user_id')
                ->pluck('total_pv', 'user_id')
                ->toArray();
        }

        $beneficiaires = $rows->groupBy('user_id')->map(function ($items, $userId) use ($pvByUser, $period) {
            $user = $items->first()->user;
            $parrain = $user?->parrain;

            $direct = round((float) $items->where('type', 'direct')->sum('amount'), 2);
            $indirect = round((float) $items->where('type', 'indirect')->sum('amount'), 2);
            $leadership = round((float) $items->where('type', 'leadership')->sum('amount'), 2);
            $cashPos = round((float) $items->where('type', 'cash_pos')->sum('amount'), 2);

            return [
                'name' => $user?->name ?? 'N/A',
                'id' => $userId,
                'code' => $user?->sponsor_id ?? 'N/A',
                'parrain_code' => $parrain?->sponsor_id ?? 'N/A',
                'rank_level' => $user?->rank_level ?? 'N/A',
                'pv' => $period !== 'all'
                    ? (float) ($pvByUser[$userId] ?? 0)
                    : (float) ($user?->pv_balance ?? 0),
                'direct' => $direct,
                'indirect' => $indirect,
                'leadership' => $leadership,
                'cash_pos' => $cashPos,
                'total' => round($direct + $indirect + $leadership + $cashPos, 2),
            ];
        })->sortByDesc('total')->values();

        return [
            'commissionsCount' => $commissionsCount,
            'totals' => $totals,
            'typeStats' => $typeStats,
            'beneficiaires' => $beneficiaires,
        ];
    }

    /**
     * @return Collection<int, Commission>
     */
    public function loadCommissions(?string $period, ?callable $applyExtraFilters = null): Collection
    {
        $query = Commission::query()
            ->with([
                'user:id,name,sponsor_id,parrain_id,rank_level,pv_balance',
                'user.parrain:id,sponsor_id',
            ])
            ->whereIn('type', self::CORE_TYPES)
            ->where('amount', '>', 0)
            ->whereIn('status', ['pending', 'approved', 'paid']);

        if ($period && $period !== 'all') {
            $query->where('period', $period);
        }

        if ($applyExtraFilters) {
            $applyExtraFilters($query);
        }

        return $query
            ->select(['id', 'user_id', 'type', 'amount', 'period', 'status'])
            ->orderBy('user_id')
            ->orderBy('type')
            ->get();
    }

    /**
     * @return Collection<int, CommissionHistory>
     */
    public function loadHistory(?string $period): Collection
    {
        $query = CommissionHistory::query()
            ->with([
                'user:id,name,sponsor_id,parrain_id,rank_level,pv_balance',
                'user.parrain:id,sponsor_id',
            ])
            ->whereIn('type', self::CORE_TYPES)
            ->where('amount', '>', 0);

        if ($period && $period !== 'all') {
            $query->where('period', $period);
        }

        return $query
            ->select(['id', 'user_id', 'type', 'amount', 'period'])
            ->orderBy('user_id')
            ->orderBy('type')
            ->get();
    }

    public function filename(string $period): string
    {
        $periodSlug = preg_replace('/[^0-9A-Za-z_-]+/', '_', $period) ?: 'all';

        return 'rapport_global_commissions_'.$periodSlug.'_'.now()->format('Ymd_His').'.pdf';
    }
}
