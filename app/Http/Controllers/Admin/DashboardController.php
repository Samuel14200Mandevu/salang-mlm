<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Commission;
use App\Models\Transaction;
use App\Models\Package;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques globales
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $totalCommissions = Commission::where('status', 'paid')->sum('amount');
        $pendingCommissions = Commission::where('status', 'pending')->sum('amount');
        $totalPackages = Package::count();
        $totalProducts = Product::count();
        $totalWithdrawn = Withdrawal::where('status', 'completed')->sum('amount');
        $totalWalletBalance = Wallet::sum('balance');
        
        // Utilisateurs récents
        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
        
        // Dernières commissions
        $recentCommissions = Commission::with(['user', 'fromUser'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Dernières transactions
        $recentTransactions = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Statistiques par mois (pour le graphique)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyData[] = [
                'month' => $month->format('M'),
                'users' => User::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
                'commissions' => Commission::where('status', 'paid')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount'),
            ];
        }
        
        // Répartition des packages
        $packageDistribution = Package::withCount('users')->get();
        
        // Répartition des commissions par type
        $commissionBreakdown = Commission::where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->get();
        
        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'totalCommissions',
            'pendingCommissions',
            'totalPackages',
            'totalProducts',
            'totalWithdrawn',
            'totalWalletBalance',
            'recentUsers',
            'recentCommissions',
            'recentTransactions',
            'monthlyData',
            'packageDistribution',
            'commissionBreakdown'
        ));
    }
}
