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
use App\Models\PVHistory;
use App\Services\MLM\AdvancedRankCalculator;
use App\Services\MLM\UserCommissionDisplayService;
use App\Support\MlmRank;

class DashboardController extends Controller
{
    public function __construct(
        protected UserCommissionDisplayService $commissionDisplay
    ) {}
    /**
     * Afficher le tableau de bord selon le niveau
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

        // DÉTERMINER LE GRADE ACTUEL (niveau MLM 0–9)
        $rawRankLevel = $user->getAttributes()['rank_level'] ?? null;
        if ($rawRankLevel === null || $rawRankLevel === '') {
            $rawRankLevel = $userRank ? (int) $userRank->level : 1;
        }
        $currentRankLevel = max(0, min(9, (int) $rawRankLevel));
        $currentRankName = MlmRank::label($currentRankLevel);
        if ($userRank && $userRank->name) {
            $currentRankName = $userRank->name;
        } elseif (is_string($user->rank) && $user->rank !== '') {
            $currentRankName = $user->rank;
        }
        $currentRankId = $userRank ? $userRank->id : null;

        // ✅ CALCUL DES PV
        $pvPersonnel = $user->pv_balance ?? 0;
        $pvCumul = $user->team_pv ?? 0;
        $monthlyPv = $user->monthly_pv ?? 0;

        // TROUVER LE PROCHAIN GRADE
        $nextRank = Rank::where('level', '>', $currentRankLevel)
            ->where('is_active', true)
            ->orderBy('level', 'asc')
            ->first();

        // CALCUL DE LA PROGRESSION VERS LE PROCHAIN GRADE
        if ($nextRank) {
            $nextPvRequired = $nextRank->min_pv ?? 0;
            $currentMinPv = $userRank ? $userRank->min_pv : 0;
            
            $progress = 0;
            if ($nextPvRequired > $currentMinPv) {
                $progress = (($pvCumul - $currentMinPv) / ($nextPvRequired - $currentMinPv)) * 100;
                $progress = max(0, min(100, $progress));
            } else {
                $progress = ($pvCumul / max($nextPvRequired, 1)) * 100;
                $progress = min(100, $progress);
            }
            
            $pvNeeded = max(0, $nextPvRequired - $pvCumul);

            $personalTarget = $this->resolveNextRankPersonalPvTarget($nextRank);
            if ($personalTarget > 0) {
                $personalProgress = min(100, max(0, ($pvPersonnel / $personalTarget) * 100));
                $personalPvNeeded = max(0, $personalTarget - $pvPersonnel);
            } else {
                $personalProgress = 100;
                $personalPvNeeded = 0;
            }
            
            // Conditions du prochain grade
            $conditions = $this->getRankConditions($nextRank, $user);
        } else {
            $nextRank = null;
            $nextPvRequired = 0;
            $progress = 100;
            $pvNeeded = 0;
            $personalProgress = 100;
            $personalTarget = 0;
            $personalPvNeeded = 0;
            $conditions = [];
        }

        // STATISTIQUES DES COMMISSIONS (bandes A/B visibles, C masquée)
        $commissionBands = $this->commissionDisplay->summarize($user->id);
        $totalCommission = $commissionBands['paid_total_visible'];
        $pendingCommission = $commissionBands['pending_payable'];

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

        $level4Ids = User::whereIn('parrain_id', $level2Ids)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();
        $level4 = User::whereIn('parrain_id', $level4Ids)
            ->where('is_active', true)
            ->count();

        $level5Ids = User::whereIn('parrain_id', $level4Ids)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();
        $level5 = User::whereIn('parrain_id', $level5Ids)
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


        // TOP FILLEULS - Version corrigée
        $topDownlines = User::where('parrain_id', $user->id)
            ->where('user_type', 'member') 
            ->withCount(['filleuls as total_downlines'])  // ← Utilise filleuls au lieu de downlines
            ->orderBy('total_downlines', 'desc')
            ->limit(10)
            ->get();

        // Si vous voulez aussi ajouter les filleuls indirects
        $topDownlines->each(function ($downline) {
        $downline->total_indirect = User::where('parrain_id', $downline->id)
            ->where('user_type', 'member')
            ->count();
        });

        // MEMBRES RÉCENTS
        $recentMembers = User::where('id', '!=', $user->id)
            ->with(['package', 'rank'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ACTIVITÉS RÉCENTES
        $recentActivities = Commission::where('user_id', $user->id)
            ->visibleToMember()
            ->where(function($query) {
            $query->where('type', 'direct')      // Bonus direct
              ->orWhere('type', 'indirect')   // Bonus indirect
              ->orWhere('type', 'leadership') // Bonus leadership
              ->orWhere('type', 'pos_sale');   // Vente POS (PV reçus)
            })
                ->with(['fromUser', 'package', 'period'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

        // STATISTIQUES DU JOUR
        $stats = [
            'today_earnings' => Commission::where('user_id', $user->id)
                ->visibleToMember()
                ->where('status', 'paid')
                ->whereDate('created_at', today())
                ->sum('amount') ?? 0,
            'today_pv' => (float) (PVHistory::where('user_id', $user->id)
                ->whereDate('date', today())
                ->sum('amount') ?? 0),
        ];

        // Données mensuelles (PV crédités + commissions payées) — accueil & graphiques
        $monthlyData = [];
        $monthlyPvData = [];
        $performanceChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthLocalized = $month->copy()->locale(app()->getLocale());
            $monthLabel = $monthLocalized->translatedFormat('F Y');
            $monthShort = $monthLocalized->isoFormat('MMM YY');

            $commissionAmount = (float) (Commission::where('user_id', $user->id)
                ->visibleToMember()
                ->where('status', 'paid')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('amount') ?? 0);

            $pvAmount = (float) (PVHistory::where('user_id', $user->id)
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->where('amount', '>', 0)
                ->sum('amount') ?? 0);

            $performanceChart[] = [
                'month' => $monthLabel,
                'month_short' => $monthShort,
                'pv' => $pvAmount,
                'commission' => $commissionAmount,
            ];
            $monthlyData[] = [
                'month' => $monthLocalized->translatedFormat('M'),
                'amount' => $commissionAmount,
            ];
            $monthlyPvData[] = [
                'month' => $monthLocalized->translatedFormat('M'),
                'amount' => $pvAmount,
            ];
        }

        $recentPvActivities = PVHistory::where('user_id', $user->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

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
            'team_progress' => $progress,
            'pv_needed' => $pvNeeded,
            'team_pv_needed' => $pvNeeded,
            'current_pv' => $pvCumul,
            'next_pv' => $nextPvRequired,
            'team_target_pv' => $nextPvRequired,
            'personal_progress' => $personalProgress ?? 100,
            'personal_target_pv' => $personalTarget ?? 0,
            'personal_pv_needed' => $personalPvNeeded ?? 0,
            'pv_personnel' => $pvPersonnel,
            'pv_cumul' => $pvCumul,
            'monthly_pv' => $monthlyPv,
            'current_min_pv' => $userRank ? $userRank->min_pv : 0,
            'conditions' => $conditions,
        ];

        // HISTORIQUE DES GRADES
        $history = RankHistory::where('user_id', $user->id)
            ->with(['oldRank', 'newRank'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Données communes à tous les niveaux
        $data = [
            'user' => $user,
            'sponsor' => $sponsor,
            'totalCommission' => $totalCommission,
            'pendingCommission' => $pendingCommission,
            'totalWithdrawn' => $totalWithdrawn,
            'totalDownlines' => $totalDownlines,
            'walletBalance' => $walletBalance,
            'level1' => $level1,
            'level2' => $level2,
            'level3' => $level3,
            'level4' => $level4,
            'level5' => $level5,
            'recentMembers' => $recentMembers,
            'recentActivities' => $recentActivities,
            'rankProgress' => $rankProgress,
            'stats' => $stats,
            'monthlyData' => $monthlyData,
            'monthlyPvData' => $monthlyPvData,
            'performanceChart' => $performanceChart,
            'recentPvActivities' => $recentPvActivities,
            'lastMonthRank' => $lastMonthRank,
            'rankDistribution' => $rankDistribution,
            'history' => $history,
            'rankStats' => $rankStats,
            'currentRankName' => $currentRankName,
            'currentRankLevel' => $currentRankLevel,
            'pvPersonnel' => $pvPersonnel,
            'pvCumul' => $pvCumul,
            'topDownlines' => $topDownlines,
            'commissionBands' => $commissionBands,
        ];

        $dashboardLevelNumber = $currentRankLevel;
        $dashboardLevel = config('dashboard-levels.'.$dashboardLevelNumber)
            ?? config('dashboard-levels.1');

        $data['dashboardLevel'] = $dashboardLevel;
        $data['dashboardLevelNumber'] = $dashboardLevelNumber;
        $data['currentRankBadgeClass'] = MlmRank::badgeClass($currentRankLevel);

        return view('dashboard.index', $data);
    }

    /**
     * Cible PV personnel pour le prochain grade (conditions ou barème interne).
     */
    private function resolveNextRankPersonalPvTarget(?Rank $nextRank): float
    {
        if (!$nextRank) {
            return 0.0;
        }

        if (!empty($nextRank->conditions)) {
            foreach ($nextRank->conditions as $condition) {
                if (($condition['type'] ?? '') === 'personal_pv') {
                    return (float) ($condition['value'] ?? 0);
                }
            }
        }

        return match ((int) $nextRank->level) {
            4 => 1000.0,
            5, 6 => 5000.0,
            7 => 12000.0,
            8 => 20000.0,
            9 => 30000.0,
            default => (float) ($nextRank->min_pv ?? 0),
        };
    }

    /**
     * Récupérer les conditions du grade
     */
    private function getRankConditions($rank, $user)
    {
        $conditions = [];
        
        if (empty($rank->conditions)) {
            return $conditions;
        }
        
        foreach ($rank->conditions as $condition) {
            $met = false;
            $current = 0;
            $required = 0;
            
            if (isset($condition['type'])) {
                switch ($condition['type']) {
                    case 'personal_pv':
                        $required = $condition['value'] ?? 0;
                        $current = $user->pv_balance ?? 0;
                        $met = $current >= $required;
                        break;
                    case 'team_pv':
                        $required = $condition['value'] ?? 0;
                        $current = $user->team_pv ?? 0;
                        $met = $current >= $required;
                        break;
                    case 'branches':
                        $required = $condition['value'] ?? 0;
                        $current = $this->countActiveBranches($user);
                        $met = $current >= $required;
                        break;
                    case 'sponsors':
                        $required = $condition['value'] ?? 0;
                        $current = User::where('parrain_id', $user->id)->count();
                        $met = $current >= $required;
                        break;
                    default:
                        $met = true;
                }
            }
            
            $conditions[] = [
                'label' => $condition['label'] ?? $condition['type'] ?? 'Condition',
                'type' => $condition['type'] ?? 'unknown',
                'required' => $required,
                'current' => $current,
                'met' => $met,
            ];
        }
        
        return $conditions;
    }

    /**
     * Compter les branches actives
     */
    private function countActiveBranches($user)
    {
        $level1 = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->get();
            
        $activeBranches = 0;
        foreach ($level1 as $downline) {
            if ($this->hasActiveDownline($downline)) {
                $activeBranches++;
            }
        }
        
        return $activeBranches;
    }

    private function hasActiveDownline($user)
    {
        return User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->exists();
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

        $commissionPayload = $this->commissionDisplay->memberStatsPayload($user->id);

        $stats = [
            'total_commission' => $commissionPayload['total_commission'],
            'pending_commission' => $commissionPayload['pending_commission'],
            'commission_bands' => $commissionPayload['bands'],
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
                ->visibleToMember()
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