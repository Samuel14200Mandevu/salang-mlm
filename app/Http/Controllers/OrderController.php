<?php
// app/Http/Controllers/OrderController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\MLM\MonthlyCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\PDF;

class OrderController extends Controller
{
    protected MonthlyCommissionService $commissionService;

    public function __construct(MonthlyCommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Liste des commandes de l'utilisateur
     */
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

        // Statistiques
        $pendingCount = Order::where('user_id', $user->id)->where('status', 'pending')->count();
        $completedCount = Order::where('user_id', $user->id)->where('status', 'completed')->count();
        $totalSpent = Order::where('user_id', $user->id)->where('payment_status', 'completed')->sum('total');

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders', 'pendingCount', 'completedCount', 'totalSpent'));
    }

    /**
     * Détails d'une commande
     */
    public function show(Order $order)
    {
        if ($order->user_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $order->load(['items', 'items.product', 'items.package', 'user']);

        return view('orders.show', compact('order'));
    }

    /**
     * Annuler une commande
     */
    public function cancel(Order $order)
    {
        if ($order->user_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        if ($order->status !== 'pending' && $order->status !== 'processing') {
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

    /**
     * Afficher la facture (version HTML)
     */
    public function invoice(Order $order)
    {
        // Vérifier que l'utilisateur est propriétaire de la commande
        if ($order->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $order->load(['items', 'items.product', 'items.package', 'user']);

        // Récupérer le parrain si existe
        $sponsor = null;
        if ($order->user->parrain_id) {
            $sponsor = User::find($order->user->parrain_id);
        }

        return view('orders.invoice', compact('order', 'sponsor'));
    }

    /**
     * Télécharger la facture en PDF
     */
    public function downloadInvoice(Order $order)
    {
        // Vérifier que l'utilisateur est propriétaire de la commande
        if ($order->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $order->load(['items', 'items.product', 'items.package', 'user']);

        // Récupérer le parrain si existe
        $sponsor = null;
        if ($order->user->parrain_id) {
            $sponsor = User::find($order->user->parrain_id);
        }

        // Vérifier si DomPDF est installé
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.invoice', compact('order', 'sponsor'));
            $pdf->setPaper('a6', 'portrait');
            return $pdf->download('Facture_' . $order->order_number . '.pdf');
        }

        // Fallback si DomPDF n'est pas installé
        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $html = view('orders.invoice', compact('order', 'sponsor'))->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('a6', 'portrait');
            $dompdf->render();
            return $dompdf->stream('Facture_' . $order->order_number . '.pdf');
        }

        return back()->with('error', 'Module PDF non installé. Contactez l\'administrateur.');
    }

    /**
     * API - Liste des commandes
     */
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

    /**
     * API - Détails d'une commande
     */
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