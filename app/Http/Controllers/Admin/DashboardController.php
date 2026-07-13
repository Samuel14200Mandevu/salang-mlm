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
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            $newUsers = User::whereDate('created_at', '>=', now()->subDays(30))->count();

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
            $totalOrders = Order::count();
            $totalRevenue = Order::where('status', 'completed')->sum('total') ?? 0;
            $totalWalletBalance = Wallet::sum('balance') ?? 0;

            $topSponsors = User::select('id', 'name', 'email', 'total_sponsors')
                ->orderBy('total_sponsors', 'desc')
                ->limit(5)
                ->get();

            $monthlyData = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $monthlyData[] = (object) [
                    'month' => $month->format('M Y'),
                    'users' => User::whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->count(),
                    'commissions' => (float) Commission::where('status', 'paid')
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->sum('amount'),
                    'revenue' => (float) Order::where('status', 'completed')
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->sum('total'),
                ];
            }

            $packageDistribution = Package::withCount(['users as users_count'])
                ->orderBy('users_count', 'desc')
                ->get()
                ->map(function($package) {
                    return (object) [
                        'name' => $package->name,
                        'users_count' => $package->users_count,
                        'color' => 'blue',
                    ];
                });

            $rankDistribution = Rank::withCount(['users as users_count'])
                ->orderBy('level', 'asc')
                ->get()
                ->map(function($rank) {
                    return (object) [
                        'name' => $rank->name,
                        'level' => $rank->level,
                        'users_count' => $rank->users_count,
                        'color' => 'gray',
                    ];
                });

            $recentUsers = User::orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $recentCommissions = Commission::with(['user', 'fromUser'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $recentActivities = $this->getRecentActivities();

            return view('admin.dashboard', compact(
                'totalUsers',
                'activeUsers',
                'newUsers',
                'totalCommissions',
                'pendingCommissions',
                'totalWithdrawn',
                'totalWithdrawals',
                'pendingWithdrawals',
                'totalPackages',
                'soldPackages',
                'totalProducts',
                'totalWalletBalance',
                'totalOrders',
                'totalRevenue',
                'topSponsors',
                'monthlyData',
                'packageDistribution',
                'rankDistribution',
                'recentUsers',
                'recentCommissions',
                'recentActivities'
            ));

        } catch (\Exception $e) {
            return view('admin.dashboard', [
                'totalUsers' => 0,
                'activeUsers' => 0,
                'newUsers' => 0,
                'totalCommissions' => 0,
                'pendingCommissions' => 0,
                'totalWithdrawn' => 0,
                'totalWithdrawals' => 0,
                'pendingWithdrawals' => 0,
                'totalPackages' => 0,
                'soldPackages' => 0,
                'totalProducts' => 0,
                'totalWalletBalance' => 0,
                'totalOrders' => 0,
                'totalRevenue' => 0,
                'topSponsors' => collect(),
                'monthlyData' => [],
                'packageDistribution' => collect(),
                'rankDistribution' => collect(),
                'recentUsers' => collect(),
                'recentCommissions' => collect(),
                'recentActivities' => collect(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getRecentActivities()
    {
        $activities = collect();

        $recentUsers = User::orderBy('created_at', 'desc')->limit(3)->get()
            ->map(function($user) {
                return (object) [
                    'type' => 'user',
                    'icon' => 'user-plus',
                    'message' => "New user registered: {$user->name}",
                    'time' => $user->created_at->diffForHumans(),
                    'date' => $user->created_at,
                ];
            });

        $recentCommissions = Commission::with(['user', 'fromUser'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($commission) {
                $labels = [
                    'direct' => 'Direct',
                    'indirect' => 'Indirect',
                    'leadership' => 'Leadership',
                    'retail' => 'Retail',
                ];
                $typeLabel = $labels[$commission->type] ?? ucfirst($commission->type);
                $userName = $commission->user?->name ?? 'Unknown';
                $amount = number_format($commission->amount, 2);

                return (object) [
                    'type' => 'commission',
                    'icon' => 'coins',
                    'message' => "{$typeLabel} commission of {$amount} USD for {$userName}",
                    'time' => $commission->created_at->diffForHumans(),
                    'date' => $commission->created_at,
                ];
            });

        $recentWithdrawals = Withdrawal::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($withdrawal) {
                $status = [
                    'pending' => 'pending',
                    'completed' => 'completed',
                    'failed' => 'failed',
                ][$withdrawal->status] ?? $withdrawal->status;
                $userName = $withdrawal->user?->name ?? 'Unknown';
                $amount = number_format($withdrawal->amount, 2);

                return (object) [
                    'type' => 'withdrawal',
                    'icon' => 'credit-card',
                    'message' => "Withdrawal of {$amount} USD {$status} for {$userName}",
                    'time' => $withdrawal->created_at->diffForHumans(),
                    'date' => $withdrawal->created_at,
                ];
            });

        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($order) {
                $status = [
                    'pending' => 'pending',
                    'processing' => 'processing',
                    'completed' => 'completed',
                    'cancelled' => 'cancelled',
                ][$order->status] ?? $order->status;
                $userName = $order->user?->name ?? 'Unknown';

                return (object) [
                    'type' => 'order',
                    'icon' => 'shopping-cart',
                    'message' => "Order #{$order->order_number} {$status} by {$userName}",
                    'time' => $order->created_at->diffForHumans(),
                    'date' => $order->created_at,
                ];
            });

        $activities = $activities->merge($recentUsers)
            ->merge($recentCommissions)
            ->merge($recentWithdrawals)
            ->merge($recentOrders)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return $activities;
    }

    public function apiStats()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'totalUsers' => User::count(),
                'activeUsers' => User::where('is_active', true)->count(),
                'totalCommissions' => Commission::where('status', 'paid')->sum('amount'),
                'pendingCommissions' => Commission::where('status', 'pending')->sum('amount'),
                'totalRevenue' => Order::where('status', 'completed')->sum('total'),
                'totalOrders' => Order::count(),
            ]
        ]);
    }

    public function chartData(Request $request)
    {
        $months = $request->input('months', 6);
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $data[] = (object) [
                'month' => $month->format('M Y'),
                'users' => User::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
                'commissions' => (float) Commission::where('status', 'paid')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount'),
                'revenue' => (float) Order::where('status', 'completed')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('total'),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}