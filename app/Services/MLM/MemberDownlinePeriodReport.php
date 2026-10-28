<?php

namespace App\Services\MLM;

use App\Models\CommissionPeriod;
use App\Models\PVHistory;
use App\Models\Rank;
use App\Models\User;
use App\Models\UserMonthlyRank;
use App\Support\MlmPeriod;
use App\Support\MlmRank;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MemberDownlinePeriodReport
{
    public const FIRST_PERIOD = '2026-01';

    /**
     * Périodes MLM disponibles depuis janvier 2026 jusqu’à la période courante.
     *
     * @return list<array{value: string, label: string}>
     */
    public function availablePeriods(?Carbon $now = null): array
    {
        $now = $now ?? now();
        $current = MlmPeriod::current($now);
        $cursor = Carbon::createFromFormat('Y-m', self::FIRST_PERIOD)->startOfMonth();
        $end = Carbon::createFromFormat('Y-m', $current)->startOfMonth();

        if ($end->lt($cursor)) {
            return [];
        }

        $periods = [];
        while ($cursor->lte($end)) {
            $value = $cursor->format('Y-m');
            $periods[] = [
                'value' => $value,
                'label' => $this->periodLabel($value),
            ];
            $cursor->addMonth();
        }

        return array_reverse($periods);
    }

    public function periodLabel(string $period): string
    {
        try {
            $dates = CommissionPeriod::getPeriodDates($period);

            return sprintf(
                '%s — %s → %s',
                Carbon::createFromFormat('Y-m', $period)->locale('fr')->translatedFormat('F Y'),
                $dates['start']->format('d/m/Y'),
                $dates['end']->format('d/m/Y')
            );
        } catch (\Throwable) {
            return $period;
        }
    }

    public function isValidPeriod(string $period): bool
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $period)) {
            return false;
        }

        $current = MlmPeriod::current();
        if ($period < self::FIRST_PERIOD || $period > $current) {
            return false;
        }

        return true;
    }

    /**
     * PV du membre pour le résumé de période.
     *
     * @return array{
     *     monthly_pv: float,
     *     group_pv: float,
     *     cumulative_pv: float,
     *     cumulative_network_pv: float
     * }
     */
    public function buildMemberStats(User $viewer, string $period, ?float $groupPvFromRows = null): array
    {
        $personal = $this->personalPvByUser([$viewer->id], $period);
        $monthlyPv = round((float) ($personal[$viewer->id] ?? 0), 1);

        if ($groupPvFromRows !== null) {
            $groupPv = round($groupPvFromRows, 1);
        } else {
            $tree = $this->collectDescendantsWithGeneration($viewer->id);
            $ids = $tree->keys()->all();
            $groupPv = round(array_sum($this->personalPvByUser($ids, $period)), 1);
        }

        return [
            'monthly_pv' => $monthlyPv,
            'group_pv' => $groupPv,
            'cumulative_pv' => round((float) ($viewer->pv_balance ?? 0), 1),
            'cumulative_network_pv' => round((float) ($viewer->team_pv ?? 0), 1),
        ];
    }

    /**
     * Lignes du rapport : filleuls (tous niveaux) ayant du PV sur la période.
     *
     * @return list<array{
     *     code: string,
     *     name: string,
     *     grade: string,
     *     generation: int,
     *     monthly_pv: float,
     *     group_pv: float,
     *     cumulative_pv: float,
     *     cumulative_network_pv: float,
     *     sponsor_code: string,
     *     sponsor_name: string
     * }>
     */
    public function buildRows(User $viewer, string $period): array
    {
        $tree = $this->collectDescendantsWithGeneration($viewer->id);
        if ($tree->isEmpty()) {
            return [];
        }

        $ids = $tree->keys()->all();
        $personalPv = $this->personalPvByUser($ids, $period);
        $activeIds = array_keys(array_filter($personalPv, fn ($pv) => $pv > 0));

        if ($activeIds === []) {
            return [];
        }

        $users = User::query()
            ->whereIn('id', $activeIds)
            ->with(['parrain:id,name,sponsor_id'])
            ->get(['id', 'name', 'sponsor_id', 'parrain_id', 'rank_level', 'pv_balance', 'team_pv'])
            ->keyBy('id');

        $rankLevels = $this->rankLevelsForPeriod($activeIds, $period);
        $groupPv = $this->groupPvForActiveMembers($tree, $personalPv, $activeIds);

        $rows = [];
        foreach ($activeIds as $userId) {
            $user = $users->get($userId);
            if (! $user) {
                continue;
            }

            $level = (int) ($rankLevels[$userId] ?? $user->rank_level ?? 0);
            $rows[] = [
                'code' => (string) ($user->sponsor_id ?: '—'),
                'name' => (string) ($user->name ?: 'N/A'),
                'grade' => MlmRank::label($level),
                'generation' => (int) ($tree[$userId]['generation'] ?? 1),
                'monthly_pv' => round((float) ($personalPv[$userId] ?? 0), 1),
                'group_pv' => round((float) ($groupPv[$userId] ?? 0), 1),
                'cumulative_pv' => round((float) ($user->pv_balance ?? 0), 1),
                'cumulative_network_pv' => round((float) ($user->team_pv ?? 0), 1),
                'sponsor_code' => (string) ($user->parrain?->sponsor_id ?: '—'),
                'sponsor_name' => (string) ($user->parrain?->name ?: '—'),
            ];
        }

        usort($rows, function (array $a, array $b) {
            return [$a['generation'], -$a['monthly_pv'], $a['name']]
                <=> [$b['generation'], -$b['monthly_pv'], $b['name']];
        });

        return $rows;
    }

    /**
     * @return Collection<int, array{generation: int, parent_id: int|null}>
     */
    private function collectDescendantsWithGeneration(int $rootId): Collection
    {
        $tree = [];
        $parentGeneration = [$rootId => 0];
        $frontier = [$rootId];
        $seen = [$rootId => true];

        while ($frontier !== []) {
            $children = User::query()
                ->whereIn('parrain_id', $frontier)
                ->get(['id', 'parrain_id']);

            $nextFrontier = [];
            foreach ($children as $child) {
                if (isset($seen[$child->id])) {
                    continue;
                }
                $seen[$child->id] = true;
                $parentId = (int) $child->parrain_id;
                $generation = ((int) ($parentGeneration[$parentId] ?? 0)) + 1;
                $parentGeneration[$child->id] = $generation;
                $tree[$child->id] = [
                    'generation' => $generation,
                    'parent_id' => $parentId,
                ];
                $nextFrontier[] = $child->id;
            }
            $frontier = $nextFrontier;
        }

        return collect($tree);
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, float>
     */
    private function personalPvByUser(array $userIds, string $period): array
    {
        if ($userIds === []) {
            return [];
        }

        $fromRanks = UserMonthlyRank::query()
            ->where('period', $period)
            ->whereIn('user_id', $userIds)
            ->pluck('pv_monthly', 'user_id')
            ->map(fn ($v) => (float) $v)
            ->all();

        $missing = array_values(array_diff($userIds, array_keys($fromRanks)));
        $fromHistory = [];
        if ($missing !== []) {
            $fromHistory = PVHistory::query()
                ->where('period', $period)
                ->whereIn('user_id', $missing)
                ->selectRaw('user_id, SUM(amount) as total')
                ->groupBy('user_id')
                ->pluck('total', 'user_id')
                ->map(fn ($v) => (float) $v)
                ->all();
        }

        $currentPeriod = MlmPeriod::current();
        $result = $fromRanks + $fromHistory;

        if ($period === $currentPeriod) {
            $stillMissing = array_values(array_diff($userIds, array_keys($result)));
            if ($stillMissing !== []) {
                $live = User::query()
                    ->whereIn('id', $stillMissing)
                    ->pluck('monthly_pv', 'id')
                    ->map(fn ($v) => (float) $v)
                    ->all();
                $result += $live;
            }
        }

        return $result;
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, int>
     */
    private function rankLevelsForPeriod(array $userIds, string $period): array
    {
        $rankIds = UserMonthlyRank::query()
            ->where('period', $period)
            ->whereIn('user_id', $userIds)
            ->pluck('rank_id', 'user_id')
            ->all();

        if ($rankIds === []) {
            return User::query()
                ->whereIn('id', $userIds)
                ->pluck('rank_level', 'id')
                ->map(fn ($v) => (int) $v)
                ->all();
        }

        $levelsByRankId = Rank::query()
            ->whereIn('id', array_unique(array_filter($rankIds)))
            ->pluck('level', 'id')
            ->all();

        $out = [];
        foreach ($userIds as $userId) {
            $rankId = $rankIds[$userId] ?? null;
            if ($rankId && isset($levelsByRankId[$rankId])) {
                $out[$userId] = (int) $levelsByRankId[$rankId];
            }
        }

        $missing = array_values(array_diff($userIds, array_keys($out)));
        if ($missing !== []) {
            $live = User::query()
                ->whereIn('id', $missing)
                ->pluck('rank_level', 'id')
                ->map(fn ($v) => (int) $v)
                ->all();
            $out += $live;
        }

        return $out;
    }

    /**
     * PV groupe = somme des PV personnels des descendants sur la période.
     *
     * @param  Collection<int, array{generation: int, parent_id: int|null}>  $tree
     * @param  array<int, float>  $personalPv
     * @param  list<int>  $activeIds
     * @return array<int, float>
     */
    private function groupPvForActiveMembers(Collection $tree, array $personalPv, array $activeIds): array
    {
        $childrenMap = [];
        foreach ($tree as $id => $meta) {
            $parentId = $meta['parent_id'];
            if ($parentId === null) {
                continue;
            }
            $childrenMap[$parentId][] = $id;
        }

        $memo = [];
        $sumDescendants = function (int $userId) use (&$sumDescendants, &$memo, $childrenMap, $personalPv): float {
            if (array_key_exists($userId, $memo)) {
                return $memo[$userId];
            }
            $total = 0.0;
            foreach ($childrenMap[$userId] ?? [] as $childId) {
                $total += (float) ($personalPv[$childId] ?? 0);
                $total += $sumDescendants($childId);
            }

            return $memo[$userId] = $total;
        };

        $out = [];
        foreach ($activeIds as $userId) {
            $out[$userId] = $sumDescendants($userId);
        }

        return $out;
    }
}
