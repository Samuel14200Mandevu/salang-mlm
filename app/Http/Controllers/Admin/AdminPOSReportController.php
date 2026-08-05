<?php
// app/Http/Controllers/Admin/AdminPOSReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminPOSReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Dashboard des rapports POS
     */
    public function index(Request $request)
    {
        // Statistiques globales
        $stats = $this->getGlobalStats();
        
        // Ventes par jour (derniers 7 jours)
        $dailySales = $this->getDailySales(7);
        
        // Ventes par mois (derniers 12 mois)
        $monthlySales = $this->getMonthlySales(12);
        
        // Top 5 des meilleurs caissiers
        $topCashiers = $this->getTopCashiers(5);
        
        // Top 5 des meilleurs parrains (PV générés)
        $topSponsors = $this->getTopSponsors(5);
        
        // ✅ Dernières commandes POS avec toutes les relations
        $recentOrders = Order::with(['user', 'cashier', 'creator', 'items'])
            ->where('source', 'pos')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Filtres
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        
        return view('admin.pos-reports.index', compact(
            'stats',
            'dailySales',
            'monthlySales',
            'topCashiers',
            'topSponsors',
            'recentOrders',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Rapports des ventes POS
     */
    public function sales(Request $request)
    {
        $query = Order::with(['user', 'cashier', 'creator', 'items'])
            ->where('source', 'pos')
            ->where('status', 'completed');
        
        // Filtres
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($sub) use ($search) {
                      $sub->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Statistiques des ventes
        $salesStats = [
            'total_orders' => $query->count(),
            'total_amount' => $query->sum('total'),
            'total_tax' => $query->sum('tax'),
            'total_pv' => $this->calculateTotalPV($query->get()),
            'average_order' => $query->avg('total') ?? 0,
        ];
        
        // Liste des caissiers pour le filtre
        $cashiers = User::whereHas('roles', function($q) {
            $q->where('name', 'cashier');
        })->get(['id', 'name']);
        
        return view('admin.pos-reports.sales', compact('orders', 'salesStats', 'cashiers'));
    }

    /**
     * Rapports des commissions POS
     */
    public function commissions(Request $request)
    {
        $query = Commission::with(['user', 'fromUser', 'order'])
            ->where('source', 'pos');
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $commissionStats = [
            'total_amount' => $query->sum('amount'),
            'paid_amount' => $query->where('status', 'paid')->sum('amount'),
            'pending_amount' => $query->where('status', 'pending')->sum('amount'),
            'total_members' => $query->distinct('user_id')->count('user_id'),
            'average_commission' => $query->avg('amount') ?? 0,
        ];
        
        $members = User::whereHas('commissions', function($q) {
            $q->where('source', 'pos');
        })->get(['id', 'name', 'sponsor_id']);
        
        return view('admin.pos-reports.commissions', compact('commissions', 'commissionStats', 'members'));
    }

    /**
     * Exporter les données (CSV)
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'cashier', 'creator'])
            ->where('source', 'pos')
            ->where('status', 'completed');
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $query->get();
        
        $filename = 'rapport_pos_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // En-têtes
            fputcsv($file, [
                'Date',
                'N° Commande',
                'Client',
                'Caissier',
                'Sous-total',
                'TVA',
                'Total',
                'PV Total',
                'Commission CASH'
            ]);
            
            foreach ($orders as $order) {
                $commission = Commission::where('order_id', $order->id)
                    ->where('source', 'pos')
                    ->first();
                
                // Récupérer le nom du caissier
                $cashierName = $order->cashier_name;
                
                fputcsv($file, [
                    $order->created_at->format('d/m/Y H:i'),
                    $order->order_number,
                    $order->user->name ?? 'N/A',
                    $cashierName,
                    number_format($order->subtotal, 2),
                    number_format($order->tax, 2),
                    number_format($order->total, 2),
                    $this->calculateOrderPV($order),
                    $commission ? number_format($commission->amount, 2) : '0.00'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporter en PDF (via DomPDF)
     */
    public function exportPdf(Request $request)
    {
        $query = Order::with(['user', 'cashier', 'creator'])
            ->where('source', 'pos')
            ->where('status', 'completed');
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $query->get();
        $stats = $this->getGlobalStats();
        
        $pdf = Pdf::loadView('admin.pos-reports.pdf', compact('orders', 'stats'));
        return $pdf->download('rapport_pos_' . date('Y-m-d') . '.pdf');
    }

    // ============================================================
    // MÉTHODES PRIVÉES
    // ============================================================

    private function getGlobalStats()
    {
        return [
            'total_orders' => Order::where('source', 'pos')->where('status', 'completed')->count(),
            'total_sales' => Order::where('source', 'pos')->where('status', 'completed')->sum('total'),
            'total_commissions' => Commission::where('source', 'pos')->where('status', 'paid')->sum('amount'),
            'total_pv' => $this->calculateTotalPV(Order::where('source', 'pos')->where('status', 'completed')->get()),
            'total_cashiers' => User::role('cashier')->count(),
            'total_customers' => User::where('user_type', 'client')->count(),
            'today_sales' => Order::where('source', 'pos')->where('status', 'completed')->whereDate('created_at', today())->sum('total'),
            'today_orders' => Order::where('source', 'pos')->where('status', 'completed')->whereDate('created_at', today())->count(),
        ];
    }

    private function getDailySales($days = 7)
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $day = $date->format('d/m');
            $total = Order::where('source', 'pos')
                ->where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total');
            $count = Order::where('source', 'pos')
                ->where('status', 'completed')
                ->whereDate('created_at', $date)
                ->count();
            $data[] = [
                'date' => $day,
                'total' => $total,
                'count' => $count,
            ];
        }
        return $data;
    }

    private function getMonthlySales($months = 12)
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->format('M Y');
            $total = Order::where('source', 'pos')
                ->where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total');
            $count = Order::where('source', 'pos')
                ->where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $data[] = [
                'month' => $monthName,
                'total' => $total,
                'count' => $count,
            ];
        }
        return $data;
    }

    private function getTopCashiers($limit = 5)
    {
        return User::role('cashier')
            ->withCount(['orders' => function($q) {
                $q->where('source', 'pos')->where('status', 'completed');
            }])
            ->withSum(['orders' => function($q) {
                $q->where('source', 'pos')->where('status', 'completed');
            }], 'total')
            ->having('orders_count', '>', 0)
            ->orderBy('orders_sum_total', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getTopSponsors($limit = 5)
    {
        return User::whereHas('commissions', function($q) {
            $q->where('source', 'pos')->where('status', 'paid');
        })
        ->withSum(['commissions' => function($q) {
            $q->where('source', 'pos')->where('status', 'paid');
        }], 'amount')
        ->having('commissions_sum_amount', '>', 0)
        ->orderBy('commissions_sum_amount', 'desc')
        ->limit($limit)
        ->get();
    }

    private function calculateTotalPV($orders)
    {
        $total = 0;
        foreach ($orders as $order) {
            $total += $this->calculateOrderPV($order);
        }
        return $total;
    }

    private function calculateOrderPV($order)
    {
        return $order->items->sum(function($item) {
            return ($item->pv_value ?? 0) * $item->quantity;
        });
    }
}