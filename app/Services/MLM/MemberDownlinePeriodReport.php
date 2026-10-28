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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
     * Lignes du rapport réseau : tous les filleuls (tous niveaux), PV cumulés.
     * Résultat mis en cache 15 min pour éviter de reconstruire un gros réseau.
     *
     * @return list<array{
     *     code: string,
     *     name: string,
     *     grade: string,
     *     generation: int,
     *     cumulative_pv: float,
     *     cumulative_network_pv: float,
     *     sponsor_code: string,
     *     sponsor_name: string
     * }>
     */
    public function buildNetworkRows(User $viewer): array
    {
        return Cache::remember(
            'member-network-report:'.$viewer->id,
            now()->addMinutes(15),
            fn () => $this->buildNetworkRowsUncached($viewer)
        );
    }

    /**
     * @return list<array{
     *     code: string,
     *     name: string,
     *     grade: string,
     *     generation: int,
     *     cumulative_pv: float,
     *     cumulative_network_pv: float,
     *     sponsor_code: string,
     *     sponsor_name: string
     * }>
     */
    public function buildNetworkRowsUncached(User $viewer): array
    {
        $sortedIds = $this->cachedNetworkSortedIds($viewer);
        if ($sortedIds === []) {
            return [];
        }

        $tree = $this->cachedNetworkTree($viewer);
        $rows = $this->hydrateNetworkRows($sortedIds, $tree);

        usort($rows, function (array $a, array $b) {
            return [$a['generation'], -$a['cumulative_pv'], $a['name']]
                <=> [$b['generation'], -$b['cumulative_pv'], $b['name']];
        });

        return $rows;
    }

    /**
     * Aperçu paginé : charge seulement les filleuls de la page (pas tout le HTML).
     *
     * @return array{
     *     pageRows: list<array<string, mixed>>,
     *     page: int,
     *     totalPages: int,
     *     rowOffset: int,
     *     rowsCount: int
     * }
     */
    public function buildNetworkPage(User $viewer, int $page): array
    {
        // Même taille de lot pour toutes les pages (évite des pages à demi vides).
        $pageSize = 25;

        $sortedIds = $this->cachedNetworkSortedIds($viewer);
        $count = count($sortedIds);

        if ($count === 0) {
            return [
                'pageRows' => [],
                'page' => 1,
                'totalPages' => 1,
                'rowOffset' => 0,
                'rowsCount' => 0,
            ];
        }

        $totalPages = max(1, (int) ceil($count / $pageSize));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $pageSize;
        $size = $pageSize;

        $pageIds = array_slice($sortedIds, $offset, $size);
        $tree = $this->cachedNetworkTree($viewer);
        $pageRows = $this->hydrateNetworkRows($pageIds, $tree);

        return [
            'pageRows' => $pageRows,
            'page' => $page,
            'totalPages' => $totalPages,
            'rowOffset' => $offset,
            'rowsCount' => $count,
        ];
    }

    /**
     * @return list<int>
     */
    private function cachedNetworkSortedIds(User $viewer): array
    {
        return Cache::remember(
            'member-network-sorted-ids:'.$viewer->id,
            now()->addMinutes(15),
            function () use ($viewer) {
                $tree = $this->cachedNetworkTree($viewer);
                $ids = $tree->keys()->all();
                usort($ids, function ($a, $b) use ($tree) {
                    return [
                        (int) ($tree[$a]['generation'] ?? 0),
                        (int) $a,
                    ] <=> [
                        (int) ($tree[$b]['generation'] ?? 0),
                        (int) $b,
                    ];
                });

                return $ids;
            }
        );
    }

    /**
     * @return Collection<int, array{generation: int, parent_id: int|null}>
     */
    private function cachedNetworkTree(User $viewer): Collection
    {
        return Cache::remember(
            'member-network-tree:'.$viewer->id,
            now()->addMinutes(15),
            fn () => $this->collectDescendantsWithGeneration($viewer->id)
        );
    }

    /**
     * @param  list<int>  $ids
     * @param  Collection<int, array{generation: int, parent_id: int|null}>  $tree
     * @return list<array<string, mixed>>
     */
    private function hydrateNetworkRows(array $ids, Collection $tree): array
    {
        if ($ids === []) {
            return [];
        }

        $usersById = [];
        foreach (array_chunk($ids, 400) as $chunk) {
            $batch = DB::table('users as u')
                ->leftJoin('users as p', 'p.id', '=', 'u.parrain_id')
                ->whereIn('u.id', $chunk)
                ->get([
                    'u.id',
                    'u.name',
                    'u.sponsor_id',
                    'u.rank_level',
                    'u.pv_balance',
                    'u.team_pv',
                    'p.sponsor_id as sponsor_code',
                    'p.name as sponsor_name',
                ]);

            foreach ($batch as $row) {
                $usersById[(int) $row->id] = $row;
            }
        }

        $rows = [];
        foreach ($ids as $userId) {
            $user = $usersById[$userId] ?? null;
            if (! $user) {
                continue;
            }

            $rows[] = [
                'code' => (string) ($user->sponsor_id ?: '—'),
                'name' => (string) ($user->name ?: 'N/A'),
                'grade' => MlmRank::label((int) ($user->rank_level ?? 0)),
                'generation' => (int) ($tree[$userId]['generation'] ?? 1),
                'cumulative_pv' => round((float) ($user->pv_balance ?? 0), 1),
                'cumulative_network_pv' => round((float) ($user->team_pv ?? 0), 1),
                'sponsor_code' => (string) ($user->sponsor_code ?: '—'),
                'sponsor_name' => (string) ($user->sponsor_name ?: '—'),
            ];
        }

        return $rows;
    }

    /**
     * @return array{cumulative_pv: float, cumulative_network_pv: float}
     */
    public function buildMemberCumulativeStats(User $viewer): array
    {
        return [
            'cumulative_pv' => round((float) ($viewer->pv_balance ?? 0), 1),
            'cumulative_network_pv' => round((float) ($viewer->team_pv ?? 0), 1),
        ];
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
            $nextFrontier = [];
            foreach (array_chunk($frontier, 400) as $frontierChunk) {
                $children = User::query()
                    ->whereIn('parrain_id', $frontierChunk)
                    ->get(['id', 'parrain_id']);

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
