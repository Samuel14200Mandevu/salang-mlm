<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Commission;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Package;
use App\Models\Rank;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load(['rank', 'package', 'wallet']);

        if (!$user) {
            return redirect()->route('login');
        }

        $sponsor = User::find($user->parrain_id);

        $totalCommission = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount') ?? 0;

        $pendingCommission = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount') ?? 0;

        $totalWithdrawn = Withdrawal::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount') ?? 0;

        $totalDownlines = User::where('parrain_id', $user->id)->count();
        $walletBalance = $user->wallet ? $user->wallet->balance : 0;

        $level1 = User::where('parrain_id', $user->id)->count();

        $level1Ids = User::where('parrain_id', $user->id)->pluck('id')->toArray();
        $level2 = User::whereIn('parrain_id', $level1Ids)->count();

        $level2Ids = User::whereIn('parrain_id', $level1Ids)->pluck('id')->toArray();
        $level3 = User::whereIn('parrain_id', $level2Ids)->count();

        $recentMembers = User::where('id', '!=', $user->id)
            ->with(['package', 'rank'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentActivities = Commission::where('user_id', $user->id)
            ->with('fromUser')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $currentRank = $user->rank;

        if (is_string($currentRank)) {
            $currentRank = Rank::where('name', $currentRank)->first();
        }

        $currentRankName = $currentRank && !is_string($currentRank) ? $currentRank->name : ($user->rank ?? 'Distributor');
        $currentRankLevel = $currentRank && !is_string($currentRank) ? $currentRank->level : 1;

        $nextRank = Rank::where('min_pv', '>', ($user->pv_balance ?? 0))
            ->where('is_active', true)
            ->orderBy('min_pv', 'asc')
            ->first();

        // ✅ CALCUL DES PV
        $pvPersonnel = $user->pv_balance ?? 0;
        $pvEquipe = $user->team_pv ?? 0;
        $pvCumul = $pvPersonnel + $pvEquipe;
        $monthlyPv = $user->monthly_pv ?? 0;

        $rankProgress = [
            'current' => $currentRankName,
            'current_level' => $currentRankLevel,
            'next' => $nextRank ? $nextRank->name : 'Maximum Level',
            'next_level' => $nextRank ? $nextRank->level : $currentRankLevel,
            'progress' => $nextRank ? min(100, (($pvCumul) / max($nextRank->min_pv, 1)) * 100) : 100,
            'pv_needed' => $nextRank ? max(0, $nextRank->min_pv - $pvCumul) : 0,
            'current_pv' => $pvCumul,
            'next_pv' => $nextRank ? $nextRank->min_pv : $pvCumul,
            'pv_personnel' => $pvPersonnel,
            'pv_equipe' => $pvEquipe,
            'pv_cumul' => $pvCumul,
            'monthly_pv' => $monthlyPv,
        ];

        $stats = [
            'today_earnings' => Commission::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereDate('created_at', today())
                ->sum('amount') ?? 0,
        ];

        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $amount = Commission::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('amount') ?? 0;
            $monthlyData[] = [
                'month' => $month->format('M'),
                'amount' => $amount,
            ];
        }

        return view('dashboard', compact(
            'user',
            'sponsor',
            'totalCommission',
            'pendingCommission',
            'totalWithdrawn',
            'totalDownlines',
            'walletBalance',
            'level1',
            'level2',
            'level3',
            'recentMembers',
            'recentActivities',
            'rankProgress',
            'stats',
            'monthlyData'
        ));
    }

    public function apiStats()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $pvPersonnel = $user->pv_balance ?? 0;
        $pvEquipe = $user->team_pv ?? 0;
        $pvCumul = $pvPersonnel + $pvEquipe;

        $stats = [
            'total_commission' => Commission::where('user_id', $user->id)
                ->where('status', 'paid')
                ->sum('amount') ?? 0,
            'pending_commission' => Commission::where('user_id', $user->id)
                ->where('status', 'pending')
                ->sum('amount') ?? 0,
            'total_withdrawn' => Withdrawal::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('amount') ?? 0,
            'total_downlines' => User::where('parrain_id', $user->id)->count(),
            'wallet_balance' => $user->wallet ? $user->wallet->balance : 0,
            'rank' => $this->getUserRankName($user),
            'pv_personnel' => $pvPersonnel,
            'pv_equipe' => $pvEquipe,
            'pv_cumul' => $pvCumul,
            'monthly_pv' => $user->monthly_pv ?? 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function chartData(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $months = $request->input('months', 6);
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $amount = Commission::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('amount') ?? 0;

            $data[] = [
                'month' => $month->format('M Y'),
                'amount' => $amount,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function getUserRankName($user): string
    {
        if ($user->relationLoaded('rank') && $user->rank && !is_string($user->rank)) {
            return $user->rank->name;
        }

        if (is_string($user->rank)) {
            return $user->rank;
        }

        if ($user->rank_id) {
            $rank = Rank::find($user->rank_id);
            if ($rank) {
                return $rank->name;
            }
        }

        return 'Distributor';
    }
}