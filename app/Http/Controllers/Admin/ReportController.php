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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Dashboard des rapports
     */
    public function index(Request $request)
    {
        try {
            // Période
            $period = $request->period ?? 'month';
            
            // ============================================================
            // STATISTIQUES GLOBALES
            // ============================================================
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

            // ============================================================
            // ÉVOLUTION MENSUELLE (12 mois)
            // ============================================================
            $monthlySales = $this->getMonthlyData();

            // ============================================================
            // COMMISSIONS PAR TYPE
            // ============================================================
            $commissionByType = Commission::where('status', 'paid')
                ->select('type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get();

            // ============================================================
            // UTILISATEURS PAR GRADE - Version avec rank_id et relation
            // ============================================================
            $usersByRank = User::select('rank_id', DB::raw('count(*) as count'))
                ->whereNotNull('rank_id')
                ->with('rank')
                ->groupBy('rank_id')
                ->get()
                ->map(function($item) {
                    return (object) [
                        'rank' => $item->rank ? $item->rank->name : 'Non défini',
                        'count' => $item->count,
                    ];
                });

            // ============================================================
            // TOP PERFORMERS
            // ============================================================
            $topSponsors = User::orderBy('total_sponsors', 'desc')
                ->limit(10)
                ->get(['id', 'name', 'email', 'total_sponsors', 'total_earnings']);

            $topEarners = User::orderBy('total_earnings', 'desc')
                ->limit(10)
                ->get(['id', 'name', 'email', 'total_earnings', 'total_sponsors']);

            // ============================================================
            // REVENUS PAR PACKAGE
            // ============================================================
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

            // ============================================================
            // ACTIVITÉ RÉCENTE
            // ============================================================
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
            \Log::error('Erreur rapports: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return view('admin.reports.index', [
                'error' => 'Erreur: ' . $e->getMessage(),
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

    /**
     * Rapport des ventes
     */
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

    /**
     * Rapport des commissions
     */
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

    /**
     * Rapport des utilisateurs
     */
    public function users(Request $request)
    {
        $query = User::with(['rank', 'package', 'wallet']);

        if ($request->filled('rank')) {
            $query->where('rank', $request->rank);
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
            'total_earnings' => User::sum('total_earnings') ?? 0,
            'with_package' => User::whereNotNull('package_id')->count(),
            'without_package' => User::whereNull('package_id')->count(),
            'kyc_verified' => User::where('kyc_status', 'verified')->count(),
            'kyc_pending' => User::where('kyc_status', 'pending')->count(),
        ];

        $ranks = User::distinct()->pluck('rank')->filter();
        $packages = Package::all();
        $kycStatuses = ['not_submitted', 'pending', 'partial', 'verified', 'rejected'];

        return view('admin.reports.users', compact('users', 'stats', 'ranks', 'packages', 'kycStatuses'));
    }

    /**
     * Rapport des retraits
     */
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

    /**
     * Exporter un rapport
     */
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

    /**
     * Exporter les utilisateurs
     */
    private function exportUsers($request)
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
                'Nom' => $user->name,
                'Email' => $user->email,
                'Téléphone' => $user->phone ?? '',
                'Code Parrain' => $user->sponsor_id,
                'Grade' => $user->rank ?? 'Distributor',
                'Package' => $user->package->name ?? 'Aucun',
                'PV' => $user->pv_balance ?? 0,
                'BV' => $user->bv_balance ?? 0,
                'Gains Totaux' => number_format($user->total_earnings ?? 0, 2),
                'Parrainages' => $user->total_sponsors ?? 0,
                'Équipe' => $user->total_team ?? 0,
                'Solde Wallet' => number_format($user->wallet->balance ?? 0, 2),
                'Statut' => $user->is_active ? 'Actif' : 'Inactif',
                'KYC' => $user->kyc_status ?? 'Non soumis',
                'Inscrit le' => $user->created_at->format('Y-m-d'),
            ];
        })->toArray();
    }

    /**
     * Exporter les commissions
     */
    private function exportCommissions($request)
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
                'Utilisateur' => $commission->user->name ?? 'N/A',
                'De' => $commission->fromUser->name ?? 'N/A',
                'Type' => $commission->type,
                'Montant' => number_format($commission->amount, 2),
                'Pourcentage' => $commission->percentage . '%',
                'Description' => $commission->description ?? '',
                'Statut' => $commission->status,
                'Payé le' => $commission->paid_at ? $commission->paid_at->format('Y-m-d H:i') : 'En attente',
                'Date' => $commission->created_at->format('Y-m-d H:i'),
            ];
        })->toArray();
    }

    /**
     * Exporter les commandes
     */
    private function exportOrders($request)
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
                'Numéro' => $order->order_number,
                'Client' => $order->user->name ?? 'N/A',
                'Email' => $order->user->email ?? 'N/A',
                'Sous-total' => number_format($order->subtotal, 2),
                'TVA' => number_format($order->tax, 2),
                'Livraison' => number_format($order->shipping, 2),
                'Total' => number_format($order->total, 2),
                'Statut Commande' => $order->status,
                'Statut Paiement' => $order->payment_status,
                'Méthode Paiement' => $order->payment_method ?? 'N/A',
                'Date' => $order->created_at->format('Y-m-d H:i'),
            ];
        })->toArray();
    }

    /**
     * Exporter les retraits
     */
    private function exportWithdrawals($request)
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
                'Utilisateur' => $withdrawal->user->name ?? 'N/A',
                'Email' => $withdrawal->user->email ?? 'N/A',
                'Montant Demandé' => number_format($withdrawal->amount, 2),
                'Frais (2.5%)' => number_format($withdrawal->fee, 2),
                'Net' => number_format($withdrawal->net_amount, 2),
                'Méthode' => $withdrawal->method,
                'Statut' => $withdrawal->status,
                'Date' => $withdrawal->created_at->format('Y-m-d H:i'),
                'Complété le' => $withdrawal->completed_at ? $withdrawal->completed_at->format('Y-m-d H:i') : 'En attente',
            ];
        })->toArray();
    }

    /**
     * Obtenir la plage de dates
     */
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

    /**
     * Obtenir les données mensuelles
     */
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

    /**
     * Obtenir l'activité récente
     */
    private function getRecentActivity()
    {
        $activities = [];

        // Dernières inscriptions
        $users = User::orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($users as $user) {
            $activities[] = [
                'type' => 'user_registered',
                'user' => $user->name,
                'description' => "Nouvel utilisateur inscrit : {$user->name}",
                'time' => $user->created_at,
                'icon' => 'user-plus',
                'color' => 'success',
            ];
        }

        // Dernières commissions
        $commissions = Commission::where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        foreach ($commissions as $commission) {
            $activities[] = [
                'type' => 'commission_paid',
                'user' => $commission->user->name ?? 'N/A',
                'description' => "Commission de $" . number_format($commission->amount, 2) . " payée à {$commission->user->name}",
                'time' => $commission->created_at,
                'icon' => 'coins',
                'color' => 'warning',
            ];
        }

        // Derniers retraits
        $withdrawals = Withdrawal::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        foreach ($withdrawals as $withdrawal) {
            $activities[] = [
                'type' => 'withdrawal_processed',
                'user' => $withdrawal->user->name ?? 'N/A',
                'description' => "Retrait de $" . number_format($withdrawal->amount, 2) . " traité pour {$withdrawal->user->name}",
                'time' => $withdrawal->created_at,
                'icon' => 'arrow-up-right-from-square',
                'color' => 'info',
            ];
        }

        // Dernières commandes
        $orders = Order::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        foreach ($orders as $order) {
            $activities[] = [
                'type' => 'order_completed',
                'user' => $order->user->name ?? 'N/A',
                'description' => "Commande #{$order->order_number} de $" . number_format($order->total, 2) . " complétée",
                'time' => $order->created_at,
                'icon' => 'shopping-cart',
                'color' => 'primary',
            ];
        }

        // Trier par date
        usort($activities, function($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        // Limiter à 10 activités
        return array_slice($activities, 0, 10);
    }
}