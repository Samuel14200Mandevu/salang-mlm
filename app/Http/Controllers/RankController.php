<?php
// app/Http/Controllers/RankController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rank;
use App\Models\RankHistory;
use App\Models\UserMonthlyRank;
use App\Models\Commission;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RankController extends Controller
{
    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        $this->rankCalculator = $rankCalculator;
    }

    public function index()
    {
        $user = Auth::user();

        $ranks = Rank::where('is_active', true)
            ->orderBy('level', 'asc')
            ->get();

        $currentRank = $user->rank;

        $nextRank = Rank::where('level', '>', ($currentRank?->level ?? 0))
            ->where('is_active', true)
            ->orderBy('level', 'asc')
            ->first();

        $progress = $nextRank ?
            min(100, (($user->pv_balance ?? 0) / max($nextRank->min_pv, 1)) * 100) :
            100;

        $pvNeeded = $nextRank ?
            max(0, $nextRank->min_pv - ($user->pv_balance ?? 0)) :
            0;

        $currentPv = $user->pv_balance ?? 0;
        $monthlyPv = $user->monthly_pv ?? 0;
        $teamPv = $user->team_pv ?? 0;
        $nextPv = $nextRank ? $nextRank->min_pv : $currentPv;

        $conditions = $this->getRankConditions($nextRank, $user);

        $lastMonth = now()->subMonth()->format('Y-m');
        $lastMonthRank = UserMonthlyRank::where('user_id', $user->id)
            ->where('period', $lastMonth)
            ->with('rank')
            ->first();

        $history = RankHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $rankStats = [
            'total_promotions' => RankHistory::where('user_id', $user->id)
                ->whereHas('newRank', function($q) {
                    $q->where('level', '>', DB::raw('(SELECT level FROM ranks WHERE id = rank_history.old_rank_id)'));
                })
                ->count(),
            'highest_rank' => $user->rankHistory()
                ->with('newRank')
                ->orderBy('created_at', 'desc')
                ->first()?->newRank?->name ?? ($user->rank_name ?? 'Distributor'),
            'rank_history_count' => $user->rankHistory()->count(),
        ];

        $rankDistribution = Rank::withCount('users')
            ->where('is_active', true)
            ->orderBy('level', 'asc')
            ->get()
            ->mapWithKeys(function($rank) {
                return [$rank->name => $rank->users_count];
            });

        return view('rank.index', compact(
            'user',
            'ranks',
            'currentRank',
            'nextRank',
            'progress',
            'pvNeeded',
            'currentPv',
            'monthlyPv',
            'teamPv',
            'nextPv',
            'conditions',
            'lastMonthRank',
            'history',
            'rankStats',
            'rankDistribution'
        ));
    }

    private function getRankConditions($rank, $user)
    {
        if (!$rank) return null;

        $conditions = [];
        $rankLevel = $rank->level;

        $conditionRules = [
            4 => [
                ['type' => 'personal_pv', 'value' => 1000, 'label' => 'Personal PV >= 1000'],
                ['type' => 'branches', 'branches' => 3, 'rank_level' => 3, 'group_pv' => 1000, 'label' => '3 branches Level 3 + 1000 PV group'],
                ['type' => 'branches', 'branches' => 2, 'rank_level' => 3, 'group_pv' => 2200, 'label' => '2 branches Level 3 + 2200 PV group'],
            ],
            5 => [
                ['type' => 'branches', 'branches' => 3, 'rank_level' => 4, 'group_pv' => 3800, 'label' => '3 branches Level 4 + 3800 PV group'],
                ['type' => 'branches', 'branches' => 2, 'rank_level' => 4, 'group_pv' => 7800, 'label' => '2 branches Level 4 + 7800 PV group'],
                ['type' => 'branches_mixed', 'branches' => [2 => 4, 4 => 3], 'group_pv' => 3800, 'label' => '2 branches Level 4 + 4 branches Level 3 + 3800 PV group'],
                ['type' => 'branches_mixed', 'branches' => [1 => 4, 6 => 3], 'group_pv' => 3800, 'label' => '1 branch Level 4 + 6 branches Level 3 + 3800 PV group'],
            ],
            6 => [
                ['type' => 'branches', 'branches' => 3, 'rank_level' => 5, 'group_pv' => 16000, 'label' => '3 branches Level 5 + 16000 PV group'],
                ['type' => 'branches', 'branches' => 2, 'rank_level' => 5, 'group_pv' => 35000, 'label' => '2 branches Level 5 + 35000 PV group'],
                ['type' => 'branches_mixed', 'branches' => [2 => 5, 4 => 4], 'group_pv' => 16000, 'label' => '2 branches Level 5 + 4 branches Level 4 + 16000 PV group'],
                ['type' => 'branches_mixed', 'branches' => [1 => 5, 6 => 4], 'group_pv' => 16000, 'label' => '1 branch Level 5 + 6 branches Level 4 + 16000 PV group'],
            ],
            7 => [
                ['type' => 'branches', 'branches' => 3, 'rank_level' => 6, 'group_pv' => 73000, 'label' => '3 branches Level 6 + 73000 PV group'],
                ['type' => 'branches', 'branches' => 2, 'rank_level' => 6, 'group_pv' => 145000, 'label' => '2 branches Level 6 + 145000 PV group'],
                ['type' => 'branches_mixed', 'branches' => [2 => 6, 4 => 5], 'group_pv' => 73000, 'label' => '2 branches Level 6 + 4 branches Level 5 + 73000 PV group'],
                ['type' => 'branches_mixed', 'branches' => [1 => 6, 6 => 5], 'group_pv' => 73000, 'label' => '1 branch Level 6 + 6 branches Level 5 + 73000 PV group'],
            ],
            8 => [
                ['type' => 'branches', 'branches' => 3, 'rank_level' => 7, 'group_pv' => 280000, 'label' => '3 branches Level 7 + 280000 PV group'],
                ['type' => 'branches', 'branches' => 2, 'rank_level' => 7, 'group_pv' => 580000, 'label' => '2 branches Level 7 + 580000 PV group'],
                ['type' => 'branches_mixed', 'branches' => [2 => 7, 4 => 6], 'group_pv' => 280000, 'label' => '2 branches Level 7 + 4 branches Level 6 + 280000 PV group'],
                ['type' => 'branches_mixed', 'branches' => [1 => 7, 6 => 6], 'group_pv' => 280000, 'label' => '1 branch Level 7 + 6 branches Level 6 + 280000 PV group'],
            ],
            9 => [
                ['type' => 'branches', 'branches' => 3, 'rank_level' => 8, 'group_pv' => 400000, 'label' => '3 branches Level 8 + 400000 PV group'],
                ['type' => 'branches', 'branches' => 2, 'rank_level' => 8, 'group_pv' => 780000, 'label' => '2 branches Level 8 + 780000 PV group'],
                ['type' => 'branches_mixed', 'branches' => [2 => 8, 4 => 7], 'group_pv' => 400000, 'label' => '2 branches Level 8 + 4 branches Level 7 + 400000 PV group'],
                ['type' => 'branches_mixed', 'branches' => [1 => 8, 6 => 7], 'group_pv' => 400000, 'label' => '1 branch Level 8 + 6 branches Level 7 + 400000 PV group'],
            ],
        ];

        if (isset($conditionRules[$rankLevel])) {
            foreach ($conditionRules[$rankLevel] as $rule) {
                $conditions[] = [
                    'label' => $rule['label'],
                    'met' => $this->checkCondition($user, $rule),
                ];
            }
        }

        return $conditions;
    }

    private function checkCondition($user, $rule)
    {
        $type = $rule['type'];

        if ($type === 'personal_pv') {
            return ($user->pv_balance ?? 0) >= $rule['value'];
        }

        if ($type === 'branches') {
            $branches = $this->countQualifiedBranches($user, $rule['rank_level']);
            $groupPV = $user->team_pv ?? 0;
            return $branches >= $rule['branches'] && $groupPV >= $rule['group_pv'];
        }

        if ($type === 'branches_mixed') {
            $valid = true;
            foreach ($rule['branches'] as $count => $level) {
                $branches = $this->countQualifiedBranches($user, $level);
                if ($branches < $count) {
                    $valid = false;
                    break;
                }
            }
            $groupPV = $user->team_pv ?? 0;
            return $valid && $groupPV >= $rule['group_pv'];
        }

        return false;
    }

    private function countQualifiedBranches($user, $rankLevel)
    {
        $count = 0;
        $filleuls = $user->filleuls;

        foreach ($filleuls as $filleul) {
            if ($this->hasRankLevel($filleul, $rankLevel)) {
                $count++;
            }
        }

        return $count;
    }

    private function hasRankLevel($user, $rankLevel)
    {
        $userLevel = $user->rank?->level ?? 0;

        if ($userLevel >= $rankLevel) {
            return true;
        }

        foreach ($user->filleuls as $filleul) {
            if ($this->hasRankLevel($filleul, $rankLevel)) {
                return true;
            }
        }

        return false;
    }

    public function history(Request $request)
    {
        $user = Auth::user();

        $history = RankHistory::where('user_id', $user->id)
            ->with(['oldRank', 'newRank'])
            ->when($request->filled('year'), function($query) use ($request) {
                return $query->whereYear('created_at', $request->year);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => RankHistory::where('user_id', $user->id)->count(),
            'promotions' => RankHistory::where('user_id', $user->id)
                ->whereHas('newRank', function($q) {
                    $q->where('level', '>', DB::raw('(SELECT level FROM ranks WHERE id = rank_history.old_rank_id)'));
                })
                ->count(),
            'demotions' => RankHistory::where('user_id', $user->id)
                ->whereHas('newRank', function($q) {
                    $q->where('level', '<', DB::raw('(SELECT level FROM ranks WHERE id = rank_history.old_rank_id)'));
                })
                ->count(),
        ];

        $years = RankHistory::where('user_id', $user->id)
            ->select(DB::raw('DISTINCT YEAR(created_at) as year'))
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('rank.history', compact('history', 'stats', 'years'));
    }

    public function leaderboard(Request $request)
    {
        $user = Auth::user();

        $topByPV = User::where('is_active', true)
            ->with('rank')
            ->orderBy('pv_balance', 'desc')
            ->limit(20)
            ->get()
            ->map(function($u, $index) {
                return [
                    'rank' => $index + 1,
                    'name' => $u->name,
                    'pv' => $u->pv_balance ?? 0,
                    'grade' => $u->rank_name ?? 'Distributor',
                    'is_current_user' => $u->id === auth()->id(),
                ];
            });

        $topByCommissions = Commission::where('status', 'paid')
            ->select('user_id', DB::raw('SUM(amount) as total'))
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get()
            ->map(function($item, $index) {
                return [
                    'rank' => $index + 1,
                    'name' => $item->user?->name ?? 'N/A',
                    'total' => (float) $item->total,
                    'is_current_user' => $item->user_id === auth()->id(),
                ];
            });

        $userRank = User::where('is_active', true)
            ->where('pv_balance', '>', ($user->pv_balance ?? 0))
            ->count() + 1;

        return view('rank.leaderboard', compact('topByPV', 'topByCommissions', 'userRank'));
    }

    public function apiProgress()
    {
        $user = Auth::user();

        $currentRank = $user->rank;
        $nextRank = Rank::where('level', '>', ($currentRank?->level ?? 0))
            ->where('is_active', true)
            ->orderBy('level', 'asc')
            ->first();

        $progress = $nextRank ?
            min(100, (($user->pv_balance ?? 0) / max($nextRank->min_pv, 1)) * 100) :
            100;

        return response()->json([
            'success' => true,
            'data' => [
                'current_rank' => $currentRank?->name ?? 'Distributor',
                'current_level' => $currentRank?->level ?? 1,
                'next_rank' => $nextRank?->name ?? null,
                'next_level' => $nextRank?->level ?? null,
                'progress' => $progress,
                'pv_needed' => $nextRank ? max(0, $nextRank->min_pv - ($user->pv_balance ?? 0)) : 0,
                'current_pv' => $user->pv_balance ?? 0,
                'next_pv' => $nextRank ? $nextRank->min_pv : ($user->pv_balance ?? 0),
            ]
        ]);
    }

    public function apiHistory()
    {
        $user = Auth::user();

        $history = RankHistory::where('user_id', $user->id)
            ->with(['oldRank', 'newRank'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
}