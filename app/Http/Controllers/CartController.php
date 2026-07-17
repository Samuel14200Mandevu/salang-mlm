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

        // ✅ RÉCUPÉRER LE WALLET DE L'UTILISATEUR
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

        // ✅ RÉCUPÉRER LE WALLET
        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return back()->with('error', 'Wallet not found. Please contact support.');
        }

        // ✅ VÉRIFIER LE SOLDE
        if ($wallet->balance < $total) {
            return back()->with('error', 'Insufficient balance. You have $' . number_format($wallet->balance, 2) . ' and the total is $' . number_format($total, 2) . '.');
        }

        DB::beginTransaction();

        try {
            // ✅ 1. DÉDUIRE L'ARGENT DU WALLET
            $balanceBefore = $wallet->balance;
            $wallet->balance -= $total;
            $wallet->save();

            // ✅ 2. CRÉER LA COMMANDE
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
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
            $hasPackage = false;
            $packageForCommission = null;

            // ✅ 3. CRÉER LES ITEMS
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

                if ($item['type'] == 'package') {
                    $hasPackage = true;
                    $packageForCommission = Package::find($item['id']);
                }

                if ($item['type'] == 'product') {
                    $product = Product::find($item['id']);
                    if ($product) {
                        $product->stock -= $item['quantity'];
                        $product->save();
                    }
                }
            }

            // ✅ 4. METTRE À JOUR LES PV
            $user->pv_balance += $totalPV;
            $user->bv_balance += $totalBV;
            $user->monthly_pv += $totalPV;
            $user->monthly_bv += $totalBV;
            $user->save();

            // ✅ 5. TRANSACTION DE PAIEMENT
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
                'description' => 'Order #' . $order->order_number,
                'metadata' => json_encode([
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'items_count' => count($cart),
                ]),
                'completed_at' => now(),
            ]);

            // ✅ 6. METTRE À JOUR L'ÉQUIPE
            $user->updateTeamPVWithoutEvents();
            $user->updateAllAncestorsWithoutEvents();
            $user->calculateAndUpdateRank();

            // ✅ 7. CALCULER LES COMMISSIONS
            if ($hasPackage && $packageForCommission) {
                $this->calculateCommissionsForOrder($order, $user, $packageForCommission);
            }

            // ✅ 8. VIDER LE PANIER
            Session::forget('cart');
            DB::commit();

            Log::info('Order completed with payment', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'total' => $total,
                'wallet_balance_after' => $wallet->balance,
                'has_package' => $hasPackage,
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order placed successfully! $' . number_format($total, 2) . ' deducted from your wallet.');

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
     * Calculer les commissions pour une commande
     */
    private function calculateCommissionsForOrder($order, $user, $package): void
    {
        try {
            $period = CommissionPeriod::firstOrCreate(
                ['period' => date('Y-m')],
                [
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                    'status' => 'pending',
                ]
            );

            $commissions = $this->commissionDistributor->distributeCommissions(
                $user,
                $package,
                $order->id,
                $period
            );

            $totalAmount = collect($commissions)->sum('amount');

            Log::info('Commissions calculées pour la commande', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'commissions_count' => count($commissions),
                'total_amount' => $totalAmount,
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