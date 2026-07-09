<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Commission;
use App\Models\Package;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // ✅ Statistiques simples avec valeurs par défaut
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            $totalCommissions = Commission::where('status', 'paid')->sum('amount') ?? 0;
            $pendingCommissions = Commission::where('status', 'pending')->sum('amount') ?? 0;
            $totalWithdrawn = Withdrawal::where('status', 'completed')->sum('amount') ?? 0;
            $totalWithdrawals = Withdrawal::count();
            $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
            $totalPackages = Package::count();
            $soldPackages = Order::whereHas('items', function($q) {
                $q->whereNotNull('package_id');
            })->count();
            $totalProducts = Product::count();
            $totalWalletBalance = Wallet::sum('balance') ?? 0;
            $totalOrders = Order::count();
            $totalRevenue = Order::where('status', 'completed')->sum('total') ?? 0;

            // ✅ Données mensuelles
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
                        ->sum('amount') ?? 0,
                ];
            }

            // ✅ Distribution des packages
            $packageDistribution = Package::withCount('users')->get();

            // ✅ Activités récentes
            $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
            $recentCommissions = Commission::with(['user', 'fromUser'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

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

        } catch (\Exception $e) {
            // ✅ Si erreur, afficher un message
            return view('admin.dashboard', [
                'error' => 'Erreur: ' . $e->getMessage(),
                'totalUsers' => 0,
                'activeUsers' => 0,
                'totalCommissions' => 0,
                'pendingCommissions' => 0,
                'totalWithdrawn' => 0,
                'totalWithdrawals' => 0,
                'pendingWithdrawals' => 0,
                'totalPackages' => 0,
                'soldPackages' => 0,
                'totalProducts' => 0,
                'monthlyData' => [],
                'packageDistribution' => collect(),
                'recentUsers' => collect(),
                'recentCommissions' => collect(),
                'totalWalletBalance' => 0,
                'totalOrders' => 0,
                'totalRevenue' => 0,
            ]);
        }
    }
}