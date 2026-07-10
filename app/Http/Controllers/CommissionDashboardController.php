<?php
// app/Http/Controllers/CommissionDashboardController.php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\User;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommissionDashboardController extends Controller
{
    /**
     * Tableau de bord des commissions - DONNÉES RÉELLES
     */
    public function index()
    {
        $user = Auth::user();
        
        // ✅ 1. STATISTIQUES GÉNÉRALES
        $stats = $this->getGlobalStats($user->id);
        
        // ✅ 2. RÉPARTITION PAR TYPE
        $byType = $this->getCommissionsByType($user->id);
        
        // ✅ 3. ÉVOLUTION MENSUELLE (12 derniers mois)
        $monthly = $this->getMonthlyCommissions($user->id);
        
        // ✅ 4. DERNIÈRES COMMISSIONS
        $recent = Commission::where('user_id', $user->id)
            ->with(['fromUser', 'package'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // ✅ 5. TOP PARRAINS (qui ont généré le plus de commissions)
        $topReferrals = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select('from_user_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('from_user_id')
            ->with('fromUser')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        
        // ✅ 6. COMMISSIONS EN ATTENTE
        $pending = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['fromUser', 'package'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // ✅ 7. STATISTIQUES PAR PACKAGE
        $byPackage = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->whereNotNull('package_id')
            ->select('package_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('package_id')
            ->with('package')
            ->orderBy('total', 'desc')
            ->get();
        
        // ✅ 8. MEILLEUR MOIS
        $bestMonth = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('total', 'desc')
            ->first();
        
        // ✅ 9. MOYENNE PAR COMMISSION
        $avgCommission = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->avg('amount');
        
        // ✅ 10. COMMISSIONS PAR JOUR (30 derniers jours)
        $daily = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        return view('commissions.dashboard', compact(
            'stats',
            'byType',
            'monthly',
            'recent',
            'topReferrals',
            'pending',
            'byPackage',
            'bestMonth',
            'avgCommission',
            'daily',
            'user'
        ));
    }
    
    /**
     * Statistiques globales
     */
    private function getGlobalStats($userId)
    {
        $stats = Commission::where('user_id', $userId)
            ->select(
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as paid_amount'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_amount'),
                DB::raw('COUNT(CASE WHEN status = "paid" THEN 1 END) as paid_count'),
                DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'),
                DB::raw('SUM(CASE WHEN type = "direct" THEN amount ELSE 0 END) as direct_total'),
                DB::raw('SUM(CASE WHEN type = "indirect" THEN amount ELSE 0 END) as indirect_total'),
                DB::raw('SUM(CASE WHEN type = "leadership" THEN amount ELSE 0 END) as leadership_total'),
                DB::raw('SUM(CASE WHEN type = "retail" THEN amount ELSE 0 END) as retail_total')
            )
            ->first();
        
        // Calculer les pourcentages
        $total = $stats->total_amount ?? 0;
        
        return [
            'total_count' => $stats->total_count ?? 0,
            'total_amount' => $total,
            'paid_amount' => $stats->paid_amount ?? 0,
            'pending_amount' => $stats->pending_amount ?? 0,
            'paid_count' => $stats->paid_count ?? 0,
            'pending_count' => $stats->pending_count ?? 0,
            'direct_total' => $stats->direct_total ?? 0,
            'indirect_total' => $stats->indirect_total ?? 0,
            'leadership_total' => $stats->leadership_total ?? 0,
            'retail_total' => $stats->retail_total ?? 0,
            'direct_percent' => $total > 0 ? round(($stats->direct_total ?? 0) / $total * 100, 1) : 0,
            'indirect_percent' => $total > 0 ? round(($stats->indirect_total ?? 0) / $total * 100, 1) : 0,
            'leadership_percent' => $total > 0 ? round(($stats->leadership_total ?? 0) / $total * 100, 1) : 0,
            'retail_percent' => $total > 0 ? round(($stats->retail_total ?? 0) / $total * 100, 1) : 0,
        ];
    }
    
    /**
     * Commissions par type
     */
    private function getCommissionsByType($userId)
    {
        $types = [
            'direct' => ['label' => 'Directes', 'color' => 'primary', 'icon' => 'user-friends'],
            'indirect' => ['label' => 'Indirectes', 'color' => 'warning', 'icon' => 'users'],
            'leadership' => ['label' => 'Leadership', 'color' => 'danger', 'icon' => 'crown'],
            'retail' => ['label' => 'Retail', 'color' => 'success', 'icon' => 'shopping-bag'],
        ];
        
        $data = Commission::where('user_id', $userId)
            ->where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');
        
        $result = [];
        foreach ($types as $key => $info) {
            $result[$key] = [
                'label' => $info['label'],
                'color' => $info['color'],
                'icon' => $info['icon'],
                'total' => $data->has($key) ? $data[$key]->total : 0,
                'count' => $data->has($key) ? $data[$key]->count : 0,
            ];
        }
        
        return $result;
    }
    
    /**
     * Commissions mensuelles (12 derniers mois)
     */
    private function getMonthlyCommissions($userId)
    {
        $data = Commission::where('user_id', $userId)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
        
        // Compléter les mois manquants
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthLabel = now()->subMonths($i)->format('M Y');
            $existing = $data->firstWhere('month', $month);
            
            $months[] = [
                'month' => $month,
                'label' => $monthLabel,
                'total' => $existing ? $existing->total : 0,
                'count' => $existing ? $existing->count : 0,
            ];
        }
        
        return $months;
    }
}