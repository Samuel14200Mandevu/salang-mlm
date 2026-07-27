<?php
// app/Http/Controllers/Admin/OrderController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Liste des commandes
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']);

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($sub) use ($search) {
                      $sub->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

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

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistiques
        $totalOrders = Order::count();
        $pendingCount = Order::where('status', 'pending')->count();
        $processingCount = Order::where('status', 'processing')->count();
        $completedCount = Order::where('status', 'completed')->count();
        $cancelledCount = Order::where('status', 'cancelled')->count();

        return view('admin.orders.index', compact(
            'orders',
            'totalOrders',
            'pendingCount',
            'processingCount',
            'completedCount',
            'cancelledCount'
        ));
    }

    /**
     * Détails d'une commande
     */
    public function show($id)
    {
        $order = Order::with(['user', 'items.product', 'items.package'])
            ->findOrFail($id);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Facture d'une commande
     */
    public function invoice($id)
    {
        $order = Order::with(['user', 'items.product', 'items.package'])
            ->findOrFail($id);
        
        return view('admin.orders.invoice', compact('order'));
    }

    /**
     * Mettre à jour le statut d'une commande
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        // Si la commande est terminée, mettre à jour les PV
        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            $order->updateUserPV();
        }

        Log::info('Statut de commande mis à jour', [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', 'Statut de la commande mis à jour avec succès !');
    }

    /**
     * Exporter les commandes en CSV
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'items']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->get();

        $filename = 'commandes_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // En-têtes
            fputcsv($file, [
                'ID',
                'N° Commande',
                'Client',
                'Email',
                'Sous-total',
                'TVA',
                'Total',
                'Statut',
                'Paiement',
                'Date'
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->order_number,
                    $order->user->name ?? 'N/A',
                    $order->user->email ?? 'N/A',
                    number_format($order->subtotal, 2),
                    number_format($order->tax, 2),
                    number_format($order->total, 2),
                    $order->status,
                    $order->payment_status,
                    $order->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}