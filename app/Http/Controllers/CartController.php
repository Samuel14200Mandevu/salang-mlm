<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Package;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Afficher le panier
     */
    public function index()
    {
        $cart = Session::get('cart', []);
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return view('cart.index', compact('cart', 'total'));
    }

    /**
     * Obtenir le nombre d'articles dans le panier (API)
     */
    public function count()
    {
        $cart = Session::get('cart', []);
        $count = 0;
        
        foreach ($cart as $item) {
            $count += $item['quantity'] ?? 1;
        }
        
        return response()->json(['count' => $count]);
    }

    /**
     * Ajouter un produit au panier
     */
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
                'type' => 'product'
            ];
        }

        Session::put('cart', $cart);
        
        $count = array_sum(array_column($cart, 'quantity'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => $product->name . ' ajouté au panier !'
            ]);
        }

        return back()->with('success', 'Produit ajouté au panier !');
    }

    /**
     * Ajouter un package au panier
     */
    public function addPackage(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $package = Package::find($request->package_id);
        
        if (!$package) {
            return back()->with('error', 'Package introuvable');
        }

        $cart = Session::get('cart', []);
        
        // Vérifier si le package est déjà dans le panier
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

        return back()->with('success', 'Package ajouté au panier !');
    }

    /**
     * Supprimer un article du panier
     */
    public function remove($id)
    {
        $cart = Session::get('cart', []);
        
        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
            return back()->with('success', 'Article supprimé du panier');
        }

        return back()->with('error', 'Article introuvable');
    }

    /**
     * Mettre à jour la quantité
     */
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

        return back()->with('error', 'Article introuvable');
    }

    /**
     * Vider le panier
     */
    public function clear()
    {
        Session::forget('cart');
        return redirect()->route('cart.index')->with('success', 'Panier vidé');
    }

    /**
     * Valider la commande
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Votre panier est vide');
        }

        // Calculer le total
        $total = 0;
        $subtotal = 0;
        
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        $tax = $subtotal * 0.18; // TVA 18%
        $shipping = $subtotal > 100 ? 0 : 10;
        $total = $subtotal + $tax + $shipping;

        // Créer la commande
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

        // Créer les lignes de commande
        foreach ($cart as $key => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['type'] == 'product' ? $item['id'] : null,
                'package_id' => $item['type'] == 'package' ? $item['id'] : null,
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['price'] * $item['quantity'],
            ]);

            // Mettre à jour le stock si produit
            if ($item['type'] == 'product') {
                $product = Product::find($item['id']);
                if ($product) {
                    $product->stock -= $item['quantity'];
                    $product->save();
                }
            }
        }

        // Vider le panier
        Session::forget('cart');

        // Créer une transaction
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id ?? null,
            'type' => 'purchase',
            'amount' => -$total,
            'fee' => 0,
            'net_amount' => -$total,
            'balance_before' => $user->wallet->balance ?? 0,
            'balance_after' => ($user->wallet->balance ?? 0) - $total,
            'status' => 'pending',
            'description' => 'Commande #' . $order->order_number,
        ]);

        // Traiter les commissions pour les packages
        $commissionService = new CommissionService();
        foreach ($cart as $item) {
            if ($item['type'] == 'package') {
                $commissionService->calculatePackageCommission(
                    $user->id,
                    $item['id'],
                    $order->id
                );
            }
        }

        return redirect()->route('orders.show', $order)->with('success', 'Commande validée avec succès !');
    }
}