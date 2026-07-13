<?php
// app/Http/Controllers/OrderController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
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

        if ($order->status !== 'pending') {
            return back()->with('error', 'This order cannot be cancelled.');
        }

        DB::beginTransaction();

        try {
            $order->status = 'cancelled';
            $order->save();

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

            Log::info('Order cancelled', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'order_number' => $order->order_number,
            ]);

            return redirect()->route('orders.index')
                ->with('success', 'Order cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error cancelling order: ' . $e->getMessage());
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
            return $pdf->download('invoice_' . $order->order_number . '.pdf');
        }

        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $html = view('orders.invoice', compact('order'))->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->stream('invoice_' . $order->order_number . '.pdf');
        }

        return back()->with('error', 'PDF module not installed. Please contact the administrator.');
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
                'message' => 'Unauthorized'
            ], 403);
        }

        $order->load(['items', 'items.product', 'items.package', 'user']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }
}