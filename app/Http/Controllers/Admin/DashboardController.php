<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Commission;
use App\Models\Transaction;
use App\Models\Package;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Tableau de bord admin
     */
    public function index()
    {
        // ============================================================
        // STATISTIQUES PRINCIPALES (avec cache pour performance)
        // ============================================================
        $stats = Cache::remember('admin_dashboard_stats', 300, function () {
            return [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_commissions' => Commission::where('status', 'paid')->sum('amount'),
                'pending_commissions' => Commission::where('status', 'pending')->sum('amount'),
                'total_withdrawn' => Withdrawal::where('status', 'completed')->sum('amount'),
                'total_withdrawals' => Withdrawal::count(),
                'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
                'total_packages' => Package::count(),
                'sold_packages' => Order::whereHas('items', function($q) {
                    $q->whereNotNull('package_id');
                })->count(),
                'total_products' => Product::count(),
                'total_wallet_balance' => Wallet::sum('balance'),
                'total_orders' => Order::count(),
                'total_revenue' => Order::where('status', 'completed')->sum('total'),
            ];
        });

        // ============================================================
        // DONNÉES MENSUELLES POUR LE GRAPHIQUE (6 mois)
        // ============================================================
        $monthlyData = Cache::remember('admin_monthly_data', 3600, function () {
            $data = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $data[] = [
                    'month' => $month->format('M Y'),
                    'users' => User::whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->count(),
                    'commissions' => (float) Commission::where('status', 'paid')
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->sum('amount'),
                    'sales' => (float) Order::where('status', 'completed')
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->sum('total'),
                ];
            }
            return $data;
        });

        // ============================================================
        // DISTRIBUTION DES PACKAGES
        // ============================================================
        $packageDistribution = Package::withCount('users')->get()
            ->map(function($package) {
                return [
                    'name' => $package->name,
                    'count' => $package->users_count,
                    'color' => $this->getPackageColor($package->name),
                ];
            });

        // ============================================================
        // ACTIVITÉS RÉCENTES (sans cache)
        // ============================================================
        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
        
        $recentCommissions = Commission::with(['user', 'fromUser'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentWithdrawals = Withdrawal::with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ============================================================
        // ACTIVITÉS DU JOUR
        // ============================================================
        $today = now()->toDateString();
        $todayStats = [
            'new_users' => User::whereDate('created_at', $today)->count(),
            'new_orders' => Order::whereDate('created_at', $today)->count(),
            'new_commissions' => Commission::whereDate('created_at', $today)->sum('amount'),
            'new_withdrawals' => Withdrawal::whereDate('created_at', $today)->sum('amount'),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'monthlyData',
            'packageDistribution',
            'recentUsers',
            'recentCommissions',
            'recentWithdrawals',
            'todayStats'
        ));
    }

    /**
     * Statistiques en temps réel (AJAX)
     */
    public function realtimeStats()
    {
        return response()->json([
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'commissions_today' => Commission::whereDate('created_at', today())->sum('amount'),
            'sales_today' => Order::whereDate('created_at', today())->sum('total'),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
            'pending_commissions' => Commission::where('status', 'pending')->count(),
            'online_users' => Cache::get('online_users', 0),
        ]);
    }

    /**
     * Couleur des packages
     */
    private function getPackageColor($name)
    {
        $colors = [
            'Starter' => 'primary',
            'Silver' => 'secondary',
            'Bronze' => 'warning',
            'Gold' => 'success',
            'Emerald' => 'info',
        ];
        return $colors[$name] ?? 'primary';
    }

    /**
     * Vider le cache du dashboard
     */
    public function clearCache()
    {
        Cache::forget('admin_dashboard_stats');
        Cache::forget('admin_monthly_data');
        return redirect()->route('admin.dashboard')
            ->with('success', '🧹 Cache du dashboard vidé.');
    }
}