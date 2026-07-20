<?php
// app/Http/Controllers/OrderController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\MLM\MonthlyCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected MonthlyCommissionService $commissionService;

    public function __construct(MonthlyCommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Order::where('user_id', $user->id);

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

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $order->load(['items', 'items.product', 'items.package', 'user']);

        return view('orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        if ($order->user_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        if ($order->status !== 'pending' && $order->status !== 'completed') {
            return back()->with('error', 'Cette commande ne peut pas être annulée.');
        }

        DB::beginTransaction();

        try {
            // Rembourser le wallet si la commande était payée
            if ($order->payment_status === 'completed' && $order->paid_at) {
                $wallet = Wallet::where('user_id', $order->user_id)->first();
                if ($wallet) {
                    $balanceBefore = $wallet->balance;
                    $wallet->balance += $order->total;
                    $wallet->save();

                    Transaction::create([
                        'user_id' => $order->user_id,
                        'wallet_id' => $wallet->id,
                        'type' => 'refund',
                        'amount' => $order->total,
                        'fee' => 0,
                        'net_amount' => $order->total,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $wallet->balance,
                        'status' => 'completed',
                        'description' => 'Remboursement pour la commande #' . $order->order_number,
                        'completed_at' => now(),
                    ]);
                }
            }

            $order->status = 'cancelled';
            $order->save();

            // Remettre les produits en stock
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->stock += $item->quantity;
                        $product->save();
                    }
                }
            }

            DB::commit();

            Log::info('Commande annulée', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'order_number' => $order->order_number,
                'refunded' => $order->payment_status === 'completed',
            ]);

            return redirect()->route('orders.index')
                ->with('success', 'Commande annulée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'annulation de la commande', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur lors de l\'annulation: ' . $e->getMessage());
        }
    }

    public function invoice(Order $order)
    {
        if ($order->user_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $order->load(['items', 'items.product', 'items.package', 'user']);

        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.invoice', compact('order'));
            return $pdf->download('facture_' . $order->order_number . '.pdf');
        }

        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $html = view('orders.invoice', compact('order'))->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->stream('facture_' . $order->order_number . '.pdf');
        }

        return back()->with('error', 'Module PDF non installé. Contactez l\'administrateur.');
    }

    public function apiIndex(Request $request)
    {
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)
            ->with(['items', 'items.product', 'items.package'])
            ->orderBy('created_at', 'desc')
            ->limit($request->input('limit', 20))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function apiShow(Order $order)
    {
        if ($order->user_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $order->load(['items', 'items.product', 'items.package', 'user']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }
}