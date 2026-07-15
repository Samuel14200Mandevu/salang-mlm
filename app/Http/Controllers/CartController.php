<?php
// app/Http/Controllers/CartController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
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
        $cart = Session::get('cart', []);
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return view('cart.index', compact('cart', 'total'));
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
                    'message' => 'Insufficient stock'
                ], 400);
            }
            return back()->with('error', 'Insufficient stock');
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
                'message' => $product->name . ' added to cart!'
            ]);
        }

        return back()->with('success', 'Product added to cart!');
    }

    public function addPackage(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $package = Package::find($request->package_id);

        if (!$package) {
            return back()->with('error', 'Package not found');
        }

        $cart = Session::get('cart', []);

        foreach ($cart as $key => $item) {
            if ($item['type'] == 'package' && $item['id'] == $package->id) {
                return back()->with('error', 'This package is already in the cart');
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

        return back()->with('success', 'Package added to cart!');
    }

    public function remove($id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
            return back()->with('success', 'Item removed from cart');
        }

        return back()->with('error', 'Item not found');
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
            return back()->with('success', 'Quantity updated');
        }

        return back()->with('error', 'Item not found');
    }

    public function clear()
    {
        Session::forget('cart');
        return redirect()->route('cart.index')->with('success', 'Cart cleared');
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $tax = $subtotal * 0.18;
        $shipping = $subtotal > 100 ? 0 : 10;
        $total = $subtotal + $tax + $shipping;

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'discount' => 0,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
            ]);

            $totalPV = 0;
            $totalBV = 0;
            $hasPackage = false;

            foreach ($cart as $key => $item) {
                $pvValue = $item['pv_value'] ?? 0;
                $bvValue = $item['bv_value'] ?? 0;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['type'] == 'product' ? $item['id'] : null,
                    'package_id' => $item['type'] == 'package' ? $item['id'] : null,
                    'name' => $item['name'],
                    'sku' => $item['type'] == 'product' ? 'PROD-' . $item['id'] : 'PKG-' . $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                    'pv_value' => $pvValue,
                    'bv_value' => $bvValue,
                    'options' => json_encode([
                        'type' => $item['type'],
                        'pv_value' => $pvValue,
                        'bv_value' => $bvValue,
                    ]),
                ]);

                $totalPV += $pvValue * $item['quantity'];
                $totalBV += $bvValue * $item['quantity'];

                if ($item['type'] == 'package') {
                    $hasPackage = true;
                }

                if ($item['type'] == 'product') {
                    $product = Product::find($item['id']);
                    if ($product) {
                        $product->stock -= $item['quantity'];
                        $product->save();
                    }
                }
            }

            // Mettre à jour les PV/BV de l'utilisateur
            $user->pv_balance += $totalPV;
            $user->bv_balance += $totalBV;
            $user->monthly_pv += $totalPV;
            $user->monthly_bv += $totalBV;
            $user->save();

            // Créer une transaction
            if ($user->wallet) {
                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $user->wallet->id,
                    'type' => 'purchase',
                    'amount' => -$total,
                    'fee' => 0,
                    'net_amount' => -$total,
                    'balance_before' => $user->wallet->balance,
                    'balance_after' => $user->wallet->balance,
                    'status' => 'pending',
                    'description' => 'Order #' . $order->order_number,
                ]);
            }

            // ✅ === NOUVEAU : CALCUL DES COMMISSIONS POUR LA COMMANDE ===
            if ($hasPackage) {
                $this->calculateCommissionsForOrder($order, $user);
            }

            // Mettre à jour le statut de la commande
            $order->status = 'completed';
            $order->payment_status = 'completed';
            $order->paid_at = now();
            $order->save();

            Session::forget('cart');
            DB::commit();

            Log::info('Order completed with commissions', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'total' => $total,
                'has_package' => $hasPackage,
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error placing order: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Calculer les commissions pour une commande
     */
    private function calculateCommissionsForOrder($order, $user): void
    {
        try {
            // Récupérer ou créer la période en cours
            $period = CommissionPeriod::firstOrCreate(
                ['period' => date('Y-m')],
                [
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                    'status' => 'pending',
                ]
            );

            $totalCommissions = 0;
            $commissionCount = 0;

            foreach ($order->items as $item) {
                if ($item->package_id) {
                    $package = Package::find($item->package_id);
                    if ($package) {
                        $commissions = $this->commissionDistributor->distributeCommissions(
                            $user,
                            $package,
                            $order->id,
                            $period
                        );

                        $commissionCount += count($commissions);
                        $totalCommissions += collect($commissions)->sum('amount');
                    }
                }
            }

            Log::info('Commissions calculées pour la commande', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'commissions_count' => $commissionCount,
                'total_amount' => $totalCommissions,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des commissions pour la commande', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}