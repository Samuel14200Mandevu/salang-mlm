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
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // ✅ Récupérer le sponsor
        $sponsor = User::where('sponsor_id', $user->sponsor_id)->first();

        // ✅ Statistiques
        $totalCommission = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount') ?? 0;
            
        $pendingCommission = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount') ?? 0;
            
        $totalWithdrawn = Withdrawal::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount') ?? 0;
            
        $totalDownlines = User::where('sponsor_id', $user->sponsor_id)->count();
        $walletBalance = $user->wallet ? $user->wallet->balance : 0;
        
        // ✅ Niveaux du réseau
        $level1 = User::where('sponsor_id', $user->sponsor_id)->count();
        
        $level1SponsorCodes = User::where('sponsor_id', $user->sponsor_id)
            ->pluck('sponsor_id')
            ->filter()
            ->toArray();
        $level2 = User::whereIn('sponsor_id', $level1SponsorCodes)->count();
        
        $level2SponsorCodes = User::whereIn('sponsor_id', $level1SponsorCodes)
            ->pluck('sponsor_id')
            ->filter()
            ->toArray();
        $level3 = User::whereIn('sponsor_id', $level2SponsorCodes)->count();

        // ✅ Derniers membres
        $recentMembers = User::where('id', '!=', $user->id)
            ->with(['package', 'rank'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ✅ Activités récentes
        $recentActivities = Commission::where('user_id', $user->id)
            ->with('fromUser')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ✅ Progression des grades
        $nextRank = Rank::where('min_pv', '>', $user->pv_balance)
            ->orderBy('min_pv', 'asc')
            ->first();
        
        $rankProgress = [
            'current' => $user->rank ? $user->rank->name : 'Distributor',
            'next' => $nextRank ? $nextRank->name : 'Maximum Level',
            'progress' => $nextRank ? min(100, ($user->pv_balance / max($nextRank->min_pv, 1)) * 100) : 100,
            'pv_needed' => $nextRank ? max(0, $nextRank->min_pv - $user->pv_balance) : 0,
            'current_pv' => $user->pv_balance ?? 0,
            'next_pv' => $nextRank ? $nextRank->min_pv : $user->pv_balance ?? 0,
        ];

        // ✅ Stats du jour
        $stats = [
            'today_earnings' => Commission::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereDate('created_at', today())
                ->sum('amount') ?? 0,
        ];

        // ✅ Données mensuelles pour le graphique
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
}