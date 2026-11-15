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
use App\Http\Controllers\Concerns\GeneratesPdfSafely;
use Illuminate\Support\Facades\PDF;

class OrderController extends Controller
{
    use GeneratesPdfSafely;

    protected MonthlyCommissionService $commissionService;

    public function __construct(MonthlyCommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Liste des commandes de l'utilisateur
     * (achats perso + ventes POS où son code parrain a été utilisé)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Order::query()
            ->forMember($user)
            ->with(['items', 'user']);

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

        $memberOrders = Order::query()->forMember($user);
        $pendingCount = (clone $memberOrders)->where('status', 'pending')->count();
        $completedCount = (clone $memberOrders)->where('status', 'completed')->count();
        // « Total dépensé » = uniquement les achats du membre (pas les ventes clients POS)
        $totalSpent = Order::query()
            ->where('user_id', $user->id)
            ->where('payment_status', 'completed')
            ->sum('total');

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders', 'pendingCount', 'completedCount', 'totalSpent'));
    }

    /**
     * Détails d'une commande
     */
    public function show(Order $order)
    {
        $this->authorizeMemberOrder($order);

        $order->load(['items', 'items.product', 'items.package', 'user']);

        return view('orders.show', compact('order'));
    }

    /**
     * Annuler une commande
     */
    public function cancel(Order $order)
    {
        // Annulation réservée au propriétaire (pas au parrain POS)
        if ($order->user_id != Auth::id() && ! Auth::user()->hasRole('admin')) {
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
                        'description' => 'Remboursement pour la commande #'.$order->order_number,
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
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erreur lors de l\'annulation: '.$e->getMessage());
        }
    }

    /**
     * Afficher la facture (version HTML)
     */
    public function invoice(Order $order)
    {
        $this->authorizeMemberOrder($order);

        $order->load(['items', 'items.product', 'items.package', 'user']);

        $sponsor = $this->resolveOrderSponsor($order);

        return view('orders.invoice', compact('order', 'sponsor'));
    }

    /**
     * Télécharger la facture en PDF
     */
    public function downloadInvoice(Order $order)
    {
        $this->authorizeMemberOrder($order);

        $order->load(['items', 'items.product', 'items.package', 'user']);

        $sponsor = $this->resolveOrderSponsor($order);

        try {
            return $this->downloadPdfView(
                'orders.invoice',
                compact('order', 'sponsor'),
                'Facture_'.$order->order_number.'.pdf',
                'a6',
                'portrait',
                120,
                '256M'
            );
        } catch (\Throwable $e) {
            $this->logPdfFailure('orders.invoice', $e, ['order_id' => $order->id]);

            return back()->with('error', 'Erreur lors de la génération de la facture PDF.');
        }
    }

    /**
     * API - Liste des commandes
     */
    public function apiIndex(Request $request)
    {
        $user = Auth::user();

        $orders = Order::query()
            ->forMember($user)
            ->with(['items', 'items.product', 'items.package', 'user'])
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
        if (! $order->isAccessibleBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        $order->load(['items', 'items.product', 'items.package', 'user']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    private function authorizeMemberOrder(Order $order): void
    {
        $user = Auth::user();
        if (! $user || ! $order->isAccessibleBy($user)) {
            abort(403);
        }
    }

    private function resolveOrderSponsor(Order $order): ?User
    {
        $sponsorId = data_get($order->metadata, 'sponsor_id');
        if ($sponsorId) {
            return User::find($sponsorId);
        }

        if ($order->user?->parrain_id) {
            return User::find($order->user->parrain_id);
        }

        return null;
    }
}
