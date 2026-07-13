<?php
// app/Http/Controllers/Admin/ReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Package;
use App\Models\Withdrawal;
use App\Models\Product;
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            $period = $request->period ?? 'month';

            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_commissions' => Commission::where('status', 'paid')->sum('amount') ?? 0,
                'pending_commissions' => Commission::where('status', 'pending')->sum('amount') ?? 0,
                'total_sales' => Order::where('status', 'completed')->sum('total') ?? 0,
                'total_withdrawn' => Withdrawal::where('status', 'completed')->sum('amount') ?? 0,
                'total_packages_sold' => Order::whereHas('items', function($q) {
                    $q->whereNotNull('package_id');
                })->count() ?? 0,
                'total_products' => Product::count() ?? 0,
                'total_orders' => Order::count() ?? 0,
                'avg_order_value' => Order::where('status', 'completed')->avg('total') ?? 0,
            ];

            $monthlySales = $this->getMonthlyData();

            $commissionByType = Commission::where('status', 'paid')
                ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get();

            $usersByRank = User::select('rank_id', DB::raw('count(*) as count'))
                ->whereNotNull('rank_id')
                ->with('rank')
                ->groupBy('rank_id')
                ->get()
                ->map(function($item) {
                    return (object) [
                        'rank' => $item->rank ? $item->rank->name : 'Not defined',
                        'count' => $item->count,
                    ];
                });

            $topSponsors = User::orderBy('total_sponsors', 'desc')
                ->limit(10)
                ->get(['id', 'name', 'email', 'total_sponsors', 'total_earnings']);

            $topEarners = User::orderBy('total_earnings', 'desc')
                ->limit(10)
                ->get(['id', 'name', 'email', 'total_earnings', 'total_sponsors']);

            $packageRevenue = Package::withCount('users')
                ->get()
                ->map(function($package) {
                    return (object) [
                        'name' => $package->name,
                        'users_count' => $package->users_count ?? 0,
                        'price' => $package->price ?? 0,
                        'total_revenue' => ($package->price ?? 0) * ($package->users_count ?? 0),
                    ];
                });

            $recentActivity = $this->getRecentActivity();

            return view('admin.reports.index', compact(
                'stats',
                'monthlySales',
                'commissionByType',
                'usersByRank',
                'topSponsors',
                'topEarners',
                'packageRevenue',
                'recentActivity',
                'period'
            ));

        } catch (\Exception $e) {
            \Log::error('Reports error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return view('admin.reports.index', [
                'error' => 'Error: ' . $e->getMessage(),
                'stats' => [],
                'monthlySales' => [],
                'commissionByType' => collect(),
                'usersByRank' => collect(),
                'topSponsors' => collect(),
                'topEarners' => collect(),
                'packageRevenue' => collect(),
                'recentActivity' => [],
                'period' => 'month'
            ]);
        }
    }

    public function sales(Request $request)
    {
        $query = Order::with(['user', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_total')) {
            $query->where('total', '>=', $request->min_total);
        }

        if ($request->filled('max_total')) {
            $query->where('total', '<=', $request->max_total);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total_orders' => $query->count(),
            'total_revenue' => $query->sum('total'),
            'avg_order_value' => $query->avg('total') ?? 0,
            'total_tax' => $query->sum('tax'),
            'total_shipping' => $query->sum('shipping'),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];

        $statuses = ['pending', 'processing', 'completed', 'cancelled'];
        $paymentStatuses = ['pending', 'completed', 'failed'];

        return view('admin.reports.sales', compact('orders', 'stats', 'statuses', 'paymentStatuses'));
    }

    public function commissions(Request $request)
    {
        $query = Commission::with(['user', 'fromUser']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => $query->sum('amount'),
            'average' => $query->avg('amount') ?? 0,
            'count' => $query->count(),
            'by_type' => Commission::select('type', DB::raw('SUM(amount) as total'))
                ->groupBy('type')
                ->get(),
            'total_pending' => Commission::where('status', 'pending')->sum('amount'),
            'total_paid' => Commission::where('status', 'paid')->sum('amount'),
            'pending_count' => Commission::where('status', 'pending')->count(),
            'paid_count' => Commission::where('status', 'paid')->count(),
        ];

        $types = Commission::distinct()->pluck('type');
        $statuses = ['pending', 'paid', 'cancelled'];
        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('admin.reports.commissions', compact('commissions', 'stats', 'types', 'statuses', 'users'));
    }

    public function users(Request $request)
    {
        $query = User::with(['rank', 'package', 'wallet']);

        if ($request->filled('rank_id')) {
            $query->where('rank_id', $request->rank_id);
        }

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active == '1');
        }

        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->kyc_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_pv')) {
            $query->where('pv_balance', '>=', $request->min_pv);
        }

        if ($request->filled('max_pv')) {
            $query->where('pv_balance', '<=', $request->max_pv);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'avg_pv' => User::avg('pv_balance') ?? 0,
            'avg_bv' => User::avg('bv_balance') ?? 0,
            'total_pv' => User::sum('pv_balance') ?? 0,
            'total_bv' => User::sum('bv_balance') ?? 0,
            'total_earnings' => User::sum('total_earnings') ?? 0,
            'with_package' => User::whereNotNull('package_id')->count(),
            'without_package' => User::whereNull('package_id')->count(),
            'kyc_verified' => User::where('kyc_status', 'verified')->count(),
            'kyc_pending' => User::where('kyc_status', 'pending')->count(),
        ];

        $ranks = Rank::orderBy('min_pv', 'asc')->get();
        $packages = Package::where('is_active', true)->get();
        $kycStatuses = ['not_submitted', 'pending', 'partial', 'verified', 'rejected'];

        return view('admin.reports.users', compact(
            'users',
            'stats',
            'ranks',
            'packages',
            'kycStatuses'
        ));
    }

    public function exportUsers($request)
    {
        $query = User::with(['rank', 'package']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->get()->map(function($user) {
            return [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Phone' => $user->phone ?? '',
                'Referral Code' => $user->sponsor_id,
                'Rank' => $user->rank?->name ?? 'Distributor',
                'Package' => $user->package?->name ?? 'None',
                'PV' => $user->pv_balance ?? 0,
                'BV' => $user->bv_balance ?? 0,
                'Monthly PV' => $user->monthly_pv ?? 0,
                'Monthly BV' => $user->monthly_bv ?? 0,
                'Team PV' => $user->team_pv ?? 0,
                'Team BV' => $user->team_bv ?? 0,
                'Total Earnings' => number_format($user->total_earnings ?? 0, 2),
                'Referrals' => $user->total_sponsors ?? 0,
                'Team' => $user->total_team ?? 0,
                'Wallet Balance' => number_format($user->wallet?->balance ?? 0, 2),
                'Status' => $user->is_active ? 'Active' : 'Inactive',
                'KYC' => $user->kyc_status ?? 'Not submitted',
                'Registered' => $user->created_at->format('Y-m-d'),
            ];
        })->toArray();
    }

    public function withdrawals(Request $request)
    {
        $query = Withdrawal::with(['user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => $query->sum('amount'),
            'count' => $query->count(),
            'avg_amount' => $query->avg('amount') ?? 0,
            'total_fees' => $query->sum('fee'),
            'pending' => (clone $query)->where('status', 'pending')->sum('amount'),
            'completed' => (clone $query)->where('status', 'completed')->sum('amount'),
            'failed' => (clone $query)->where('status', 'failed')->sum('amount'),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'completed_count' => (clone $query)->where('status', 'completed')->count(),
        ];

        $statuses = ['pending', 'processing', 'completed', 'failed'];
        $methods = ['crypto', 'mobile_money', 'bank'];

        return view('admin.reports.withdrawals', compact('withdrawals', 'stats', 'statuses', 'methods'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:users,commissions,orders,withdrawals',
            'format' => 'required|in:csv,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $data = [];

        switch ($request->type) {
            case 'users':
                $data = $this->exportUsers($request);
                break;
            case 'commissions':
                $data = $this->exportCommissions($request);
                break;
            case 'orders':
                $data = $this->exportOrders($request);
                break;
            case 'withdrawals':
                $data = $this->exportWithdrawals($request);
                break;
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $request->type . '_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($file, array_values($row));
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportCommissions($request)
    {
        $query = Commission::with(['user', 'fromUser']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->get()->map(function($commission) {
            return [
                'ID' => $commission->id,
                'User' => $commission->user->name ?? 'N/A',
                'From' => $commission->fromUser->name ?? 'N/A',
                'Type' => $commission->type,
                'Amount' => number_format($commission->amount, 2),
                'Percentage' => $commission->percentage . '%',
                'Description' => $commission->description ?? '',
                'Status' => $commission->status,
                'Paid At' => $commission->paid_at ? $commission->paid_at->format('Y-m-d H:i') : 'Pending',
                'Date' => $commission->created_at->format('Y-m-d H:i'),
            ];
        })->toArray();
    }

    public function exportOrders($request)
    {
        $query = Order::with(['user']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->get()->map(function($order) {
            return [
                'ID' => $order->id,
                'Order Number' => $order->order_number,
                'Customer' => $order->user->name ?? 'N/A',
                'Email' => $order->user->email ?? 'N/A',
                'Subtotal' => number_format($order->subtotal, 2),
                'Tax' => number_format($order->tax, 2),
                'Shipping' => number_format($order->shipping, 2),
                'Total' => number_format($order->total, 2),
                'Order Status' => $order->status,
                'Payment Status' => $order->payment_status,
                'Payment Method' => $order->payment_method ?? 'N/A',
                'Date' => $order->created_at->format('Y-m-d H:i'),
            ];
        })->toArray();
    }

    public function exportWithdrawals($request)
    {
        $query = Withdrawal::with(['user']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->get()->map(function($withdrawal) {
            return [
                'ID' => $withdrawal->id,
                'User' => $withdrawal->user->name ?? 'N/A',
                'Email' => $withdrawal->user->email ?? 'N/A',
                'Requested Amount' => number_format($withdrawal->amount, 2),
                'Fee (2.5%)' => number_format($withdrawal->fee, 2),
                'Net' => number_format($withdrawal->net_amount, 2),
                'Method' => $withdrawal->method,
                'Status' => $withdrawal->status,
                'Date' => $withdrawal->created_at->format('Y-m-d H:i'),
                'Completed At' => $withdrawal->completed_at ? $withdrawal->completed_at->format('Y-m-d H:i') : 'Pending',
            ];
        })->toArray();
    }

    private function getDateRange($period)
    {
        switch ($period) {
            case 'today':
                return ['start' => now()->startOfDay(), 'end' => now()->endOfDay()];
            case 'week':
                return ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()];
            case 'month':
                return ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()];
            case 'quarter':
                return ['start' => now()->startOfQuarter(), 'end' => now()->endOfQuarter()];
            case 'year':
                return ['start' => now()->startOfYear(), 'end' => now()->endOfYear()];
            default:
                return ['start' => now()->subMonth(), 'end' => now()];
        }
    }

    private function getMonthlyData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $data[] = [
                'month' => $month->format('M Y'),
                'sales' => (float) Order::where('status', 'completed')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('total'),
                'commissions' => (float) Commission::where('status', 'paid')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount'),
                'users' => User::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
                'withdrawals' => (float) Withdrawal::where('status', 'completed')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount'),
            ];
        }
        return $data;
    }

    private function getRecentActivity()
    {
        $activities = [];

        $users = User::orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($users as $user) {
            $activities[] = [
                'type' => 'user_registered',
                'user' => $user->name,
                'description' => "New user registered: {$user->name}",
                'time' => $user->created_at,
                'icon' => 'user-plus',
                'color' => 'success',
            ];
        }

        $commissions = Commission::where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        foreach ($commissions as $commission) {
            $activities[] = [
                'type' => 'commission_paid',
                'user' => $commission->user->name ?? 'N/A',
                'description' => "Commission of $" . number_format($commission->amount, 2) . " paid to {$commission->user->name}",
                'time' => $commission->created_at,
                'icon' => 'coins',
                'color' => 'warning',
            ];
        }

        $withdrawals = Withdrawal::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        foreach ($withdrawals as $withdrawal) {
            $activities[] = [
                'type' => 'withdrawal_processed',
                'user' => $withdrawal->user->name ?? 'N/A',
                'description' => "Withdrawal of $" . number_format($withdrawal->amount, 2) . " processed for {$withdrawal->user->name}",
                'time' => $withdrawal->created_at,
                'icon' => 'credit-card',
                'color' => 'info',
            ];
        }

        $orders = Order::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        foreach ($orders as $order) {
            $activities[] = [
                'type' => 'order_completed',
                'user' => $order->user->name ?? 'N/A',
                'description' => "Order #{$order->order_number} of $" . number_format($order->total, 2) . " completed",
                'time' => $order->created_at,
                'icon' => 'shopping-cart',
                'color' => 'primary',
            ];
        }

        usort($activities, function($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        return array_slice($activities, 0, 10);
    }
}