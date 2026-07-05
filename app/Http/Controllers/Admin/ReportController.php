<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Commission;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Package;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Dashboard des rapports
     */
    public function index()
    {
        // Statistiques globales
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_commissions' => Commission::where('status', 'paid')->sum('amount'),
            'pending_commissions' => Commission::where('status', 'pending')->sum('amount'),
            'total_sales' => Order::where('status', 'completed')->sum('total'),
            'total_withdrawn' => Withdrawal::where('status', 'completed')->sum('amount'),
            'total_packages_sold' => Order::whereHas('items', function($q) {
                $q->whereNotNull('package_id');
            })->count(),
        ];

        // Ventes mensuelles
        $monthlySales = $this->getMonthlySales();

        // Commissions par type
        $commissionByType = Commission::where('status', 'paid')
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->get();

        // Utilisateurs par grade
        $usersByRank = User::select('rank', DB::raw('count(*) as count'))
            ->groupBy('rank')
            ->get();

        // Top parrains
        $topSponsors = User::orderBy('total_sponsors', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'email', 'total_sponsors']);

        // Revenus par package
        $packageRevenue = Package::withCount('users')
            ->get()
            ->map(function($package) {
                $package->total_revenue = $package->price * $package->users_count;
                return $package;
            });

        return view('admin.reports.index', compact(
            'stats',
            'monthlySales',
            'commissionByType',
            'usersByRank',
            'topSponsors',
            'packageRevenue'
        ));
    }

     /**
     * Rapport des gains - ✅ AJOUTÉ
     */
    public function earnings(Request $request)
    {
        $user = auth()->user();
        
        $commissions = Commission::where('user_id', $user->id)
            ->when($request->filled('date_from'), function($q) use ($request) {
                return $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function($q) use ($request) {
                return $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $stats = [
            'total' => Commission::where('user_id', $user->id)->where('status', 'paid')->sum('amount'),
            'pending' => Commission::where('user_id', $user->id)->where('status', 'pending')->sum('amount'),
            'paid' => Commission::where('user_id', $user->id)->where('status', 'paid')->sum('amount'),
            'by_type' => Commission::where('user_id', $user->id)
                ->select('type', DB::raw('SUM(amount) as total'))
                ->groupBy('type')
                ->get(),
        ];
        
        return view('reports.earnings', compact('commissions', 'stats'));
    }

    /**
     * Rapport réseau - ✅ AJOUTÉ
     */
    public function network(Request $request)
    {
        $user = auth()->user();
        
        $downlines = User::where('sponsor_id', $user->id)
            ->when($request->filled('search'), function($q) use ($request) {
                return $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $networkStats = [
            'total' => User::where('sponsor_id', $user->id)->count(),
            'active' => User::where('sponsor_id', $user->id)->where('is_active', true)->count(),
            'inactive' => User::where('sponsor_id', $user->id)->where('is_active', false)->count(),
            'with_package' => User::where('sponsor_id', $user->id)->whereNotNull('package_id')->count(),
            'without_package' => User::where('sponsor_id', $user->id)->whereNull('package_id')->count(),
        ];
        
        return view('reports.network', compact('downlines', 'networkStats'));
    }


    /**
     * Rapport des ventes
     */
    public function sales(Request $request)
    {
        $query = Order::with(['user', 'items']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistiques
        $stats = [
            'total_orders' => $query->count(),
            'total_revenue' => $query->sum('total'),
            'avg_order_value' => $query->avg('total'),
            'total_tax' => $query->sum('tax'),
            'total_shipping' => $query->sum('shipping'),
        ];

        return view('admin.reports.sales', compact('orders', 'stats'));
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

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistiques
        $stats = [
            'total' => $query->sum('amount'),
            'average' => $query->avg('amount'),
            'by_type' => Commission::select('type', DB::raw('SUM(amount) as total'))
                ->groupBy('type')
                ->get(),
            'total_pending' => Commission::where('status', 'pending')->sum('amount'),
            'total_paid' => Commission::where('status', 'paid')->sum('amount'),
        ];

        $types = Commission::distinct()->pluck('type');
        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('admin.reports.commissions', compact('commissions', 'stats', 'types', 'users'));
    }

    /**
     * Rapport des utilisateurs
     */
    public function users(Request $request)
    {
        $query = User::with(['rank', 'package']);

        if ($request->filled('rank')) {
            $query->where('rank', $request->rank);
        }

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active == '1');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistiques
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'avg_pv' => User::avg('pv_balance'),
            'avg_bv' => User::avg('bv_balance'),
            'total_earnings' => User::sum('total_earnings'),
            'with_package' => User::whereNotNull('package_id')->count(),
            'without_package' => User::whereNull('package_id')->count(),
        ];

        $ranks = User::distinct()->pluck('rank');
        $packages = Package::all();

        return view('admin.reports.users', compact('users', 'stats', 'ranks', 'packages'));
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

        // Générer le fichier CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $request->type . '_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // En-têtes
            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
                
                // Données
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Données mensuelles pour les graphiques
     */
    private function getMonthlySales()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $data[] = [
                'month' => $month->format('M Y'),
                'sales' => Order::where('status', 'completed')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('total'),
                'commissions' => Commission::where('status', 'paid')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount'),
                'users' => User::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
            ];
        }
        return $data;
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
                'Téléphone' => $user->phone,
                'Grade' => $user->rank,
                'Package' => $user->package->name ?? 'Aucun',
                'PV' => $user->pv_balance,
                'BV' => $user->bv_balance,
                'Gains totaux' => number_format($user->total_earnings, 2),
                'Parrainages' => $user->total_sponsors,
                'Équipe' => $user->total_team,
                'Statut' => $user->is_active ? 'Actif' : 'Inactif',
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
                'Description' => $commission->description,
                'Statut' => $commission->status,
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
                'Sous-total' => number_format($order->subtotal, 2),
                'Taxe' => number_format($order->tax, 2),
                'Livraison' => number_format($order->shipping, 2),
                'Total' => number_format($order->total, 2),
                'Statut' => $order->status,
                'Paiement' => $order->payment_status,
                'Méthode' => $order->payment_method ?? 'N/A',
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
                'Montant' => number_format($withdrawal->amount, 2),
                'Frais' => number_format($withdrawal->fee, 2),
                'Net' => number_format($withdrawal->net_amount, 2),
                'Méthode' => $withdrawal->method,
                'Statut' => $withdrawal->status,
                'Date' => $withdrawal->created_at->format('Y-m-d H:i'),
                'Complété' => $withdrawal->completed_at ? $withdrawal->completed_at->format('Y-m-d H:i') : 'En attente',
            ];
        })->toArray();
    }
}