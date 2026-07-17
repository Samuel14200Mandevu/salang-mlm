<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Commission;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Package;
use App\Models\Rank;
use App\Models\RankHistory;
use App\Models\Withdrawal;
use App\Services\MLM\AdvancedRankCalculator;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord
     */
    public function index()
    {
        // RÉCUPÉRER L'UTILISATEUR AVEC SES RELATIONS
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // RAFRAÎCHIR L'UTILISATEUR DEPUIS LA BASE DE DONNÉES
        $freshUser = User::with(['rank', 'package', 'wallet'])->find($user->id);
        
        if ($freshUser) {
            $user = $freshUser;
            Auth::setUser($user);
        }

        // VIDER LE CACHE POUR AVOIR DES DONNÉES FRAÎCHES
        Cache::forget("user_rank_{$user->id}");
        Cache::forget("user_{$user->id}");
        Cache::forget("descendants_{$user->id}");
        Cache::forget("descendants_count_{$user->id}");

        // RÉCUPÉRER LE GRADE DE L'UTILISATEUR
        $userRank = $user->rank;
        
        if (is_string($userRank)) {
            $userRank = Rank::where('name', $userRank)->first();
        }
        
        if (is_null($userRank) && $user->rank_id) {
            $userRank = Rank::find($user->rank_id);
        }

        // DÉTERMINER LE GRADE ACTUEL
        $currentRankName = $userRank ? $userRank->name : ($user->rank ?? 'Distributeur');
        $currentRankLevel = $userRank ? $userRank->level : 1;
        $currentRankId = $userRank ? $userRank->id : null;

        // ✅ CALCUL DES PV (CORRIGÉ)
        // team_pv = CUMUL = PV Personnel + Somme des PV de tous les descendants
        $pvPersonnel = $user->pv_balance ?? 0;
        $pvCumul = $user->team_pv ?? 0;      // ← team_pv = CUMUL complet
        $monthlyPv = $user->monthly_pv ?? 0;

        // TROUVER LE PROCHAIN GRADE (BASÉ SUR LE CUMUL = team_pv)
        $nextRank = Rank::where('level', '>', $currentRankLevel)
            ->where('is_active', true)
            ->orderBy('level', 'asc')
            ->first();

        // CALCUL DE LA PROGRESSION VERS LE PROCHAIN GRADE
        if ($nextRank) {
            $nextPvRequired = $nextRank->min_pv ?? 0;
            $currentMinPv = $userRank ? $userRank->min_pv : 0;
            
            // Progression basée sur le CUMUL (team_pv)
            $progress = 0;
            if ($nextPvRequired > $currentMinPv) {
                $progress = (($pvCumul - $currentMinPv) / ($nextPvRequired - $currentMinPv)) * 100;
                $progress = max(0, min(100, $progress));
            } else {
                $progress = ($pvCumul / max($nextPvRequired, 1)) * 100;
                $progress = min(100, $progress);
            }
            
            $pvNeeded = max(0, $nextPvRequired - $pvCumul);
        } else {
            $nextRank = null;
            $nextPvRequired = 0;
            $progress = 100;
            $pvNeeded = 0;
        }

        // STATISTIQUES DES COMMISSIONS
        $totalCommission = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount') ?? 0;

        $pendingCommission = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount') ?? 0;

        $totalWithdrawn = Withdrawal::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount') ?? 0;

        // STATISTIQUES DU RÉSEAU
        $totalDownlines = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->count();

        $level1Ids = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();
        $level1 = count($level1Ids);

        $level2Ids = User::whereIn('parrain_id', $level1Ids)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();
        $level2 = count($level2Ids);

        $level3 = User::whereIn('parrain_id', $level2Ids)
            ->where('is_active', true)
            ->count();

        // PORTEFEUILLE
        $wallet = $user->wallet;
        $walletBalance = $wallet ? $wallet->balance : 0;

        // PARRAIN
        $sponsor = null;
        if ($user->parrain_id) {
            $sponsor = User::find($user->parrain_id);
        }

        // MEMBRES RÉCENTS
        $recentMembers = User::where('id', '!=', $user->id)
            ->with(['package', 'rank'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ACTIVITÉS RÉCENTES
        $recentActivities = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package', 'period'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // STATISTIQUES DU JOUR
        $stats = [
            'today_earnings' => Commission::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereDate('created_at', today())
                ->sum('amount') ?? 0,
        ];

        // DONNÉES MENSUELLES POUR LE GRAPHIQUE
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

        // GRADE DU MOIS DERNIER
        $lastMonthRank = RankHistory::where('user_id', $user->id)
            ->where('created_at', '<', now()->startOfMonth())
            ->orderBy('created_at', 'desc')
            ->first();

        // DISTRIBUTION DES GRADES
        $rankDistribution = User::where('is_active', true)
            ->select('rank', DB::raw('count(*) as total'))
            ->groupBy('rank')
            ->get()
            ->pluck('total', 'rank')
            ->toArray();

        // STATISTIQUES DES GRADES
        $rankStats = [
            'total_promotions' => RankHistory::where('user_id', $user->id)
                ->whereHas('newRank', function($q) {
                    $q->whereColumn('new_rank_id', '>', 'old_rank_id');
                })
                ->count(),
        ];

        // RANK PROGRESS COMPLET
        $rankProgress = [
            'current' => $currentRankName,
            'current_level' => $currentRankLevel,
            'current_id' => $currentRankId,
            'next' => $nextRank ? $nextRank->name : 'Maximum Level',
            'next_level' => $nextRank ? $nextRank->level : $currentRankLevel,
            'progress' => $progress,
            'pv_needed' => $pvNeeded,
            'current_pv' => $pvCumul,
            'next_pv' => $nextPvRequired,
            'pv_personnel' => $pvPersonnel,
            'pv_cumul' => $pvCumul,
            'monthly_pv' => $monthlyPv,
            'current_min_pv' => $userRank ? $userRank->min_pv : 0,
        ];

        // HISTORIQUE DES GRADES (pour la vue rank)
        $history = RankHistory::where('user_id', $user->id)
            ->with(['oldRank', 'newRank'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // RETOURNER LA VUE AVEC TOUTES LES DONNÉES
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
            'monthlyData',
            'lastMonthRank',
            'rankDistribution',
            'history',
            'rankStats',
            'currentRankName',
            'currentRankLevel',
            'pvPersonnel',
            'pvCumul'
        ));
    }

    /**
     * API - Statistiques du tableau de bord
     */
    public function apiStats()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // RAFRAÎCHIR L'UTILISATEUR
        $freshUser = User::with(['rank', 'wallet'])->find($user->id);
        if ($freshUser) {
            $user = $freshUser;
        }

        $userRank = $user->rank;
        if (is_string($userRank)) {
            $userRank = Rank::where('name', $userRank)->first();
        }

        $pvPersonnel = $user->pv_balance ?? 0;
        $pvCumul = $user->team_pv ?? 0;

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
            'total_downlines' => User::where('parrain_id', $user->id)
                ->where('is_active', true)
                ->count(),
            'wallet_balance' => $user->wallet ? $user->wallet->balance : 0,
            'rank' => $userRank ? $userRank->name : ($user->rank ?? 'Distributeur'),
            'rank_level' => $userRank ? $userRank->level : 1,
            'pv_personnel' => $pvPersonnel,
            'pv_cumul' => $pvCumul,
            'monthly_pv' => $user->monthly_pv ?? 0,
            'is_active' => $user->is_active,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * API - Données pour le graphique
     */
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

    /**
     * API - Vérifier le statut de l'utilisateur
     */
    public function userStatus()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // RAFRAÎCHIR L'UTILISATEUR
        $freshUser = User::find($user->id);
        
        if ($freshUser) {
            $user = $freshUser;
            Auth::setUser($user);
        }

        $userRank = $user->rank;
        if (is_string($userRank)) {
            $userRank = Rank::where('name', $userRank)->first();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'is_active' => $user->is_active,
                'rank' => $userRank ? $userRank->name : ($user->rank ?? 'Distributeur'),
                'rank_level' => $userRank ? $userRank->level : 1,
                'pv_personnel' => $user->pv_balance ?? 0,
                'pv_cumul' => $user->team_pv ?? 0,
                'monthly_pv' => $user->monthly_pv ?? 0,
            ]
        ]);
    }

    /**
     * Obtenir le nom du grade d'un utilisateur
     */
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

        return 'Distributeur';
    }

    /**
     * Obtenir le niveau du grade d'un utilisateur
     */
    private function getUserRankLevel($user): int
    {
        if ($user->relationLoaded('rank') && $user->rank && !is_string($user->rank)) {
            return $user->rank->level ?? 1;
        }

        if (is_string($user->rank)) {
            $rank = Rank::where('name', $user->rank)->first();
            if ($rank) {
                return $rank->level ?? 1;
            }
        }

        if ($user->rank_id) {
            $rank = Rank::find($user->rank_id);
            if ($rank) {
                return $rank->level ?? 1;
            }
        }

        return 1;
    }
}