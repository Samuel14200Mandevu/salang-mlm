<?php
// app/Http/Controllers/CartController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use App\Services\MLM\CommissionDistributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected MonthlyCommissionService $commissionService;
    protected CommissionDistributor $commissionDistributor;

    public function __construct(
        MonthlyCommissionService $commissionService,
        CommissionDistributor $commissionDistributor
    ) {
        $this->commissionService = $commissionService;
        $this->commissionDistributor = $commissionDistributor;
    }

    public function index()
    {
        $user = Auth::user();
        $cart = Session::get('cart', []);
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $wallet = Wallet::where('user_id', $user->id)->first();
        $walletBalance = $wallet ? $wallet->balance : 0;

        return view('cart.index', compact('cart', 'total', 'walletBalance'));
    }

    public function count()
    {
        $cart = Session::get('cart', []);
        $count = 0;

        foreach ($cart as $item) {
            $count += $item['quantity'] ?? 1;
        }

        return response()->json(['count' => $count]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::find($request->product_id);

        if (!$product || $product->stock < $request->quantity) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuffisant'
                ], 400);
            }
            return back()->with('error', 'Stock insuffisant');
        }

        $cart = Session::get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $request->quantity;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $request->quantity,
                'image' => $product->image,
                'type' => 'product',
                'pv_value' => $product->pv_value ?? 0,
                'bv_value' => $product->bv_value ?? 0,
            ];
        }

        Session::put('cart', $cart);

        $count = array_sum(array_column($cart, 'quantity'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => $product->name . ' ajouté au panier!'
            ]);
        }

        return back()->with('success', 'Produit ajouté au panier!');
    }

    public function addPackage(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $package = Package::find($request->package_id);

        if (!$package) {
            return back()->with('error', 'Package non trouvé');
        }

        $cart = Session::get('cart', []);

        foreach ($cart as $key => $item) {
            if ($item['type'] == 'package' && $item['id'] == $package->id) {
                return back()->with('error', 'Ce package est déjà dans le panier');
            }
        }

        $cart['package_' . $package->id] = [
            'id' => $package->id,
            'name' => $package->name,
            'price' => $package->price,
            'quantity' => 1,
            'pv_value' => $package->pv_value,
            'bv_value' => $package->bv_value,
            'type' => 'package'
        ];

        Session::put('cart', $cart);
        $count = array_sum(array_column($cart, 'quantity'));

        return back()->with('success', 'Package ajouté au panier!');
    }

    public function remove($id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
            return back()->with('success', 'Article supprimé du panier');
        }

        return back()->with('error', 'Article non trouvé');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Session::get('cart', []);

        if (isset($cart[$request->id])) {
            $cart[$request->id]['quantity'] = $request->quantity;
            Session::put('cart', $cart);
            return back()->with('success', 'Quantité mise à jour');
        }

        return back()->with('error', 'Article non trouvé');
    }

    public function clear()
    {
        Session::forget('cart');
        return redirect()->route('cart.index')->with('success', 'Panier vidé');
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Votre panier est vide');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // ✅ PAS DE TVA - Livraison gratuite
        $total = $subtotal;

        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return back()->with('error', 'Portefeuille non trouvé. Contactez le support.');
        }

        if ($wallet->balance < $total) {
            return back()->with('error', 'Solde insuffisant. Vous avez $' . number_format($wallet->balance, 2) . ' et le total est de $' . number_format($total, 2) . '.');
        }

        DB::beginTransaction();

        try {
            $balanceBefore = $wallet->balance;
            $wallet->balance -= $total;
            $wallet->save();

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'subtotal' => $subtotal,
                'tax' => 0,
                'shipping' => 0,
                'discount' => 0,
                'total' => $total,
                'status' => 'completed',
                'payment_status' => 'completed',
                'paid_at' => now(),
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
            ]);

            $totalPV = 0;
            $totalBV = 0;
            $itemsForCommission = [];

            foreach ($cart as $key => $item) {
                $pvValue = $item['pv_value'] ?? 0;
                $bvValue = $item['bv_value'] ?? 0;
                $itemTotal = $item['price'] * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['type'] == 'product' ? $item['id'] : null,
                    'package_id' => $item['type'] == 'package' ? $item['id'] : null,
                    'name' => $item['name'],
                    'sku' => $item['type'] == 'product' ? 'PROD-' . $item['id'] : 'PKG-' . $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $itemTotal,
                    'pv_value' => $pvValue * $item['quantity'],
                    'bv_value' => $bvValue * $item['quantity'],
                    'options' => json_encode([
                        'type' => $item['type'],
                        'pv_value' => $pvValue,
                        'bv_value' => $bvValue,
                    ]),
                ]);

                $totalPV += $pvValue * $item['quantity'];
                $totalBV += $bvValue * $item['quantity'];

                // ✅ Collecter les items pour les commissions (PACKAGES ET PRODUITS)
                $itemData = null;
                if ($item['type'] == 'package') {
                    $itemData = Package::find($item['id']);
                } elseif ($item['type'] == 'product') {
                    $itemData = Product::find($item['id']);
                }

                if ($itemData) {
                    $itemsForCommission[] = [
                        'type' => $item['type'],
                        'data' => $itemData,
                        'quantity' => $item['quantity'],
                    ];
                }

                // Mettre à jour le stock pour les produits
                if ($item['type'] == 'product') {
                    $product = Product::find($item['id']);
                    if ($product) {
                        $product->stock -= $item['quantity'];
                        $product->save();
                    }
                }
            }

            $user->pv_balance += $totalPV;
            $user->bv_balance += $totalBV;
            $user->monthly_pv += $totalPV;
            $user->monthly_bv += $totalBV;
            $user->save();

            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'purchase',
                'amount' => -$total,
                'fee' => 0,
                'net_amount' => -$total,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'description' => 'Commande #' . $order->order_number,
                'metadata' => json_encode([
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'items_count' => count($cart),
                ]),
                'completed_at' => now(),
            ]);

            $user->updateTeamPVWithoutEvents();
            $user->updateAllAncestorsWithoutEvents();
            $user->calculateAndUpdateRank();

            // ✅ CALCULER LES COMMISSIONS SEULEMENT SI L'UTILISATEUR EST ACTIF
            if ($user->is_active) {
                $this->calculateCommissionsForOrder($order, $user, $itemsForCommission);
                Log::info('Commissions calculées pour la commande', [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_status' => 'actif',
                ]);
            } else {
                Log::info('Commissions NON calculées - compte inactif', [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_status' => 'inactif',
                    'items_count' => count($itemsForCommission),
                ]);
            }

            Session::forget('cart');
            DB::commit();

            Log::info('Commande finalisée avec paiement', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'user_is_active' => $user->is_active,
                'total' => $total,
                'wallet_balance_after' => $wallet->balance,
                'items_count' => count($cart),
                'commissions_items_count' => count($itemsForCommission),
                'commissions_calculated' => $user->is_active,
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Commande passée avec succès! $' . number_format($total, 2) . ' débité de votre portefeuille.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du checkout: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Erreur lors de la commande: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Calculer les commissions pour tous les items de la commande
     */
    private function calculateCommissionsForOrder($order, $user, array $itemsForCommission): void
    {
        try {
            // Récupérer ou créer la période de commission
            $period = CommissionPeriod::firstOrCreate(
                ['period' => date('Y-m')],
                [
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                    'status' => 'pending',
                ]
            );

            $totalCommissions = 0;
            $totalCommissionCount = 0;
            $allCommissions = [];

            foreach ($itemsForCommission as $itemData) {
                $item = $itemData['data'];
                $quantity = $itemData['quantity'] ?? 1;

                // Pour chaque quantité, distribuer les commissions
                for ($i = 0; $i < $quantity; $i++) {
                    $commissions = $this->commissionDistributor->distributeCommissions(
                        $user,
                        $item,
                        $order->id,
                        $period
                    );

                    if (!empty($commissions)) {
                        $allCommissions = array_merge($allCommissions, $commissions);
                        $totalCommissions += collect($commissions)->sum('amount');
                        $totalCommissionCount += count($commissions);
                    }

                    Log::info('Commissions calculées pour l\'item', [
                        'order_id' => $order->id,
                        'item_type' => $itemData['type'],
                        'item_name' => $item->name,
                        'quantity' => $quantity,
                        'commissions_count' => count($commissions),
                        'amount' => collect($commissions)->sum('amount'),
                    ]);
                }
            }

            Log::info('Total commissions calculées pour la commande', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'total_commissions' => $totalCommissionCount,
                'total_amount' => $totalCommissions,
                'commissions_details' => collect($allCommissions)->groupBy('type')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'total' => $group->sum('amount'),
                    ];
                })->toArray(),
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des commissions', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}