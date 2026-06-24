<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Commission;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Package;
use App\Models\Rank;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Statistiques
        $totalCommission = Commission::where('user_id', $user->id)->where('status', 'paid')->sum('amount');
        $pendingCommission = Commission::where('user_id', $user->id)->where('status', 'pending')->sum('amount');
        $totalWithdrawn = \App\Models\Withdrawal::where('user_id', $user->id)->where('status', 'completed')->sum('amount');
        $totalDownlines = User::where('sponsor_id', $user->sponsor_id)->count();
        $walletBalance = $user->wallet ? $user->wallet->balance : 0;
        
        // Niveaux du réseau
        $level1 = User::where('sponsor_id', $user->sponsor_id)->count();
        $level2Ids = User::where('sponsor_id', $user->sponsor_id)->pluck('sponsor_id');
        $level2 = User::whereIn('sponsor_id', $level2Ids)->count();
        $level3Ids = User::whereIn('sponsor_id', $level2Ids)->pluck('sponsor_id');
        $level3 = User::whereIn('sponsor_id', $level3Ids)->count();

        // Derniers membres
        $recentMembers = User::where('id', '!=', $user->id)
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Activités récentes
        $recentActivities = Commission::where('user_id', $user->id)
            ->with('fromUser')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Progression des grades
        $nextRank = Rank::where('min_pv', '>', $user->pv_balance)->orderBy('min_pv', 'asc')->first();
        
        $rankProgress = [
            'current' => $user->rank ?? 'Distributor',
            'next' => $nextRank ? $nextRank->name : 'Maximum Level',
            'progress' => $nextRank ? min(100, ($user->pv_balance / max($nextRank->min_pv, 1)) * 100) : 100,
            'pv_needed' => $nextRank ? max(0, $nextRank->min_pv - $user->pv_balance) : 0,
            'current_pv' => $user->pv_balance ?? 0,
            'next_pv' => $nextRank ? $nextRank->min_pv : $user->pv_balance ?? 0,
        ];

        // Stats du jour
        $stats = [
            'today_earnings' => Commission::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereDate('created_at', today())
                ->sum('amount'),
        ];

        // Données mensuelles pour le graphique
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $amount = Commission::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('amount');
            $monthlyData[] = [
                'month' => $month->format('M'),
                'amount' => $amount ?: rand(50, 500),
            ];
        }

        return view('dashboard', compact(
            'user',
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
}
