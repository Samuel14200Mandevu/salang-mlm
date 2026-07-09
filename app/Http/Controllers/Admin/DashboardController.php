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

class DashboardController extends Controller
{
    public function index()
    {
        // ============================================================
        // STATISTIQUES PRINCIPALES
        // ============================================================
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        
        $totalCommissions = Commission::where('status', 'paid')->sum('amount');
        $pendingCommissions = Commission::where('status', 'pending')->sum('amount');
        
        $totalWithdrawn = Withdrawal::where('status', 'completed')->sum('amount');
        $totalWithdrawals = Withdrawal::count();
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        
        $totalPackages = Package::count();
        $soldPackages = Order::whereHas('items', function($q) {
            $q->whereNotNull('package_id');
        })->count();
        $totalProducts = Product::count();

        // ============================================================
        // DONNÉES MENSUELLES POUR LE GRAPHIQUE
        // ============================================================
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyData[] = [
                'month' => $month->format('M Y'),
                'users' => User::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
                'commissions' => Commission::where('status', 'paid')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount'),
            ];
        }

        // ============================================================
        // DISTRIBUTION DES PACKAGES
        // ============================================================
        $packageDistribution = Package::withCount('users')->get();

        // ============================================================
        // ACTIVITÉS RÉCENTES
        // ============================================================
        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
        
        $recentCommissions = Commission::with(['user', 'fromUser'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ============================================================
        // STATISTIQUES SUPPLÉMENTAIRES (optionnel)
        // ============================================================
        $totalWalletBalance = Wallet::sum('balance');
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', 'completed')->sum('total');

        // ============================================================
        // RENVOYER LA VUE
        // ============================================================
        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'totalCommissions',
            'pendingCommissions',
            'totalWithdrawn',
            'totalWithdrawals',
            'pendingWithdrawals',
            'totalPackages',
            'soldPackages',
            'totalProducts',
            'monthlyData',
            'packageDistribution',
            'recentUsers',
            'recentCommissions',
            'totalWalletBalance',
            'totalOrders',
            'totalRevenue'
        ));
    }
}