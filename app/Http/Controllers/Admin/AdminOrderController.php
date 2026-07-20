<?php
// app/Http/Controllers/Admin/AdminOrderController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminOrderController extends Controller
{
    public function index(Request $request)
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

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        $totalOrders = Order::count();
        $pendingCount = Order::where('status', 'pending')->count();
        $processingCount = Order::where('status', 'processing')->count();
        $completedCount = Order::where('status', 'completed')->count();
        $cancelledCount = Order::where('status', 'cancelled')->count();

        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('admin.orders.index', compact(
            'orders',
            'totalOrders',
            'pendingCount',
            'processingCount',
            'completedCount',
            'cancelledCount',
            'users'
        ));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items', 'items.product', 'items.package']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        Log::info('Order status updated by admin', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'admin_id' => auth()->id(),
        ]);

        return back()->with('success', 'Statut de la commande mis à jour avec succès.');
    }

    public function invoice(Order $order)
    {
        $order->load(['user', 'items', 'items.product', 'items.package']);

        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.orders.invoice', compact('order'));
            return $pdf->download('invoice_' . $order->order_number . '.pdf');
        }

        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $html = view('admin.orders.invoice', compact('order'))->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->stream('invoice_' . $order->order_number . '.pdf');
        }

        return back()->with('error', 'Module PDF non installé.');
    }

    public function export(Request $request)
    {
        $query = Order::with(['user']);

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

        $orders = $query->orderBy('created_at', 'desc')->get();

        $filename = 'commandes_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'N° Commande', 'Client', 'Email',
                'Sous-total', 'TVA', 'Livraison', 'Total',
                'Statut', 'Paiement', 'Date'
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->order_number,
                    $order->user?->name ?? 'N/A',
                    $order->user?->email ?? 'N/A',
                    number_format($order->subtotal, 2),
                    number_format($order->tax, 2),
                    number_format($order->shipping, 2),
                    number_format($order->total, 2),
                    $order->status,
                    $order->payment_status,
                    $order->created_at->format('Y-m-d H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}