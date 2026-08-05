<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Package;
use App\Models\Commission;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\CommissionPeriod;
use App\Jobs\UpdateTeamPV;
use App\Notifications\CommissionPaidNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf; 

class CashierController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
        
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole('cashier') && !auth()->user()->hasRole('admin')) {
                abort(403, 'Accès réservé aux caissiers.');
            }
            return $next($request);
        });
    }

    /**
     * Dashboard du caissier
     */
    public function dashboard()
    {
        $stats = [
            'total_orders_today' => Order::whereDate('created_at', today())->where('source', 'pos')->count(),
            'total_sales_today' => Order::whereDate('created_at', today())->where('source', 'pos')->sum('total'),
            'customers_today' => User::whereDate('created_at', today())->where('user_type', 'client')->count(),
            'pending_orders' => Order::where('status', 'pending')->where('source', 'pos')->count(),
        ];

        $recentOrders = Order::with(['user'])
            ->where('source', 'pos')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('cashier.dashboard', compact('stats', 'recentOrders'));
    }

    /**
     * Point de vente - Liste des produits
     */
    public function pos()
    {
        $products = Product::where('is_active', true)->get();
        $packages = Package::where('is_active', true)->get();
        return view('cashier.pos', compact('products', 'packages'));
    }

    /**
     * Page de vente d'un produit spécifique (single product)
     */
    public function posSale($productId)
    {
        $product = Product::where('is_active', true)->findOrFail($productId);
        return view('cashier.pos-sale', compact('product'));
    }

    /**
     * Trouver un produit par son ID (pour le scanner QR)
     */
    public function findProduct($id)
    {
        $product = Product::where('is_active', true)->find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé (ID: ' . $id . ')'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'pv_value' => $product->pv_value,
                'stock' => $product->stock,
                'sku' => $product->sku,
            ]
        ]);
    }

    /**
     * ✅ Trouver un produit par son SKU (code-barres)
     */
    public function findProductBySku($sku)
    {
        $product = Product::where('sku', $sku)
            ->where('is_active', true)
            ->first();
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé (SKU: ' . $sku . ')'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'pv_value' => $product->pv_value,
                'stock' => $product->stock,
                'sku' => $product->sku,
            ]
        ]);
    }

    /**
     * Créer une commande POS directement (single product)
     */
    public function createOrder(Request $request)
    {
        Log::info('=== CREATION COMMANDE POS (SINGLE PRODUCT) ===');
        
        try {
            $request->validate([
                'phone' => 'required|string',
                'name' => 'required|string',
                'sponsor_code' => 'required|string',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'nullable|integer|min:1',
                'commission_amount' => 'nullable|numeric|min:0|max:15',
                'email' => 'nullable|email',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
            ]);
            
            $sponsor = User::where('sponsor_id', $request->sponsor_code)
                ->where('is_active', true)
                ->first();
                
            if (!$sponsor) {
                return redirect()->back()->with('error', 'Code parrain invalide ou inactif');
            }
            
            $client = User::where('phone', $request->phone)->first();
            
            if (!$client) {
                $client = User::create([
                    'name' => $request->name,
                    'email' => $request->email ?? $request->phone . '@client.tmp',
                    'phone' => $request->phone,
                    'password' => bcrypt(Str::random(12)),
                    'sponsor_id' => 'CLT' . strtoupper(Str::random(6)),
                    'parrain_id' => $sponsor->id,
                    'is_active' => true,
                    'user_type' => 'client',
                    'kyc_status' => 'not_submitted',
                    'pv_balance' => 0,
                    'bv_balance' => 0,
                    'monthly_pv' => 0,
                    'monthly_bv' => 0,
                    'team_pv' => 0,
                    'team_bv' => 0,
                    'total_team' => 0,
                    'address' => $request->address,
                    'city' => $request->city,
                    'country' => $request->country,
                ]);
                
                Wallet::create([
                    'user_id' => $client->id,
                    'balance' => 0,
                    'pending_balance' => 0,
                    'currency' => 'USD',
                    'is_active' => true,
                ]);
                
                Log::info('Nouveau client POS créé', [
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'sponsor_id' => $sponsor->id,
                ]);
            } else {
                if (!$client->parrain_id) {
                    $client->parrain_id = $sponsor->id;
                }
                if ($request->filled('address')) {
                    $client->address = $request->address;
                }
                if ($request->filled('city')) {
                    $client->city = $request->city;
                }
                if ($request->filled('country')) {
                    $client->country = $request->country;
                }
                $client->save();
            }
            
            $product = Product::find($request->product_id);
            if (!$product) {
                return redirect()->back()->with('error', 'Produit non trouvé');
            }
            
            $quantity = $request->input('quantity', 1);
            $subtotal = $product->price * $quantity;
            $total = $subtotal;
            $totalPv = ($product->pv_value ?? 0) * $quantity;
            $totalBv = ($product->bv_value ?? 0) * $quantity;
            $commissionAmount = $request->input('commission_amount', 0);
            
            DB::beginTransaction();
            
            try {
                $orderNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                
                $order = Order::create([
                    'user_id' => $client->id,
                    'cashier_id' => auth()->id(),
                    'created_by' => auth()->id(),
                    'order_number' => $orderNumber,
                    'subtotal' => $subtotal,
                    'tax' => 0,
                    'shipping' => 0,
                    'discount' => 0,
                    'total' => $total,
                    'total_pv' => $totalPv,
                    'total_bv' => $totalBv,
                    'status' => 'completed',
                    'payment_status' => 'completed',
                    'payment_method' => 'cash',
                    'source' => 'pos',
                    'shipping_address' => $client->address,
                    'billing_address' => $client->address,
                    'metadata' => [
                        'cashier_id' => auth()->id(),
                        'sponsor_id' => $sponsor->id,
                        'cashier_name' => auth()->user()->name,
                        'pos_sale' => true,
                        'commission_amount' => $commissionAmount,
                    ],
                    'paid_at' => now(),
                ]);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => 'PROD-' . $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $subtotal,
                    'pv_value' => $product->pv_value ?? 0,
                    'bv_value' => $product->bv_value ?? 0,
                ]);
                
                if ($totalPv > 0 && $sponsor) {
                    $sponsor->increment('team_pv', $totalPv);
                    
                    Log::info('PV ajouté au team_pv du sponsor (POS)', [
                        'sponsor_id' => $sponsor->id,
                        'sponsor_name' => $sponsor->name,
                        'pv_added' => $totalPv,
                        'client_id' => $client->id,
                        'client_name' => $client->name,
                    ]);
                }
                
                // Commission CASH POS
                if ($commissionAmount > 0 && $commissionAmount >= 5 && $commissionAmount <= 15) {
                    Commission::create([
                        'user_id' => $sponsor->id,
                        'from_user_id' => $client->id,
                        'period' => now()->format('Y-m'),
                        'type' => 'cash_pos',
                        'source' => 'pos',
                        'amount' => $commissionAmount,
                        'percentage' => 0,
                        'description' => "Commission CASH POS - Commande #{$orderNumber}",
                        'notes' => "Montant: $" . number_format($commissionAmount, 2) . " (5$ à 15$) - Payé en espèce",
                        'order_id' => $order->id,
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                    
                    Log::info('Commission CASH POS créée', [
                        'sponsor_id' => $sponsor->id,
                        'amount' => $commissionAmount,
                        'order_id' => $order->id,
                    ]);
                }
                
                // Commission POS TRANSACTION
                Commission::create([
                    'user_id' => auth()->id(),
                    'from_user_id' => $client->id,
                    'period' => now()->format('Y-m'),
                    'type' => 'pos_transaction',
                    'source' => 'pos',
                    'amount' => 0,
                    'percentage' => 0,
                    'description' => "Vente POS - Commande #{$orderNumber}",
                    'notes' => "Client: {$client->name} - Produit: {$product->name} x{$quantity} - Total: $" . number_format($total, 2),
                    'order_id' => $order->id,
                    'status' => 'completed',
                ]);
                
                if ($client->wasRecentlyCreated) {
                    Commission::create([
                        'user_id' => $client->id,
                        'from_user_id' => $sponsor->id,
                        'period' => now()->format('Y-m'),
                        'type' => 'new_client',
                        'source' => 'pos',
                        'amount' => 0,
                        'percentage' => 0,
                        'description' => "Nouveau client - Parrain: {$sponsor->name}",
                        'notes' => "Code parrain: {$sponsor->sponsor_id}",
                        'order_id' => $order->id,
                        'status' => 'completed',
                    ]);
                }
                
                Commission::create([
                    'user_id' => $client->id,
                    'from_user_id' => $sponsor->id,
                    'period' => now()->format('Y-m'),
                    'type' => 'purchase',
                    'source' => 'pos',
                    'amount' => $total,
                    'percentage' => 0,
                    'description' => "Achat POS - Commande #{$orderNumber}",
                    'notes' => "Produit: {$product->name} x{$quantity} - Total: $" . number_format($total, 2),
                    'order_id' => $order->id,
                    'status' => 'completed',
                ]);
                
                UpdateTeamPV::dispatch($sponsor->id, true);
                $sponsor->calculateAndUpdateRank();
                
                // Sauvegarder les données pour l'impression
                $this->saveOrderData($order, $commissionAmount);
                
                DB::commit();
                
                return redirect()->route('cashier.orders.invoice', $order->id)
                    ->with('success', 'Vente #' . $orderNumber . ' validée avec succès ! Commission CASH POS payée.');
                    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur création commande POS: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur validation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Créer une commande multi-produits avec commission CASH
     */
    public function createMultiOrder(Request $request)
    {
        Log::info('=== CREATION COMMANDE POS (MULTI-PRODUITS) ===');
        
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'sponsor_code' => 'required|string|exists:users,sponsor_id',
                'commission_amount' => 'nullable|numeric|min:0|max:15',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
            ]);
            
            $cart = session()->get('pos_cart', []);
            
            if (empty($cart)) {
                return redirect()->back()->with('error', 'Votre panier est vide.');
            }
            
            $sponsor = User::where('sponsor_id', $request->sponsor_code)
                ->where('is_active', true)
                ->first();
                
            if (!$sponsor) {
                return redirect()->back()->with('error', 'Code parrain invalide ou inactif');
            }
            
            $client = User::where('phone', $request->phone)->first();
            
            if (!$client) {
                $client = User::create([
                    'name' => $request->name,
                    'email' => $request->email ?? $request->phone . '@client.tmp',
                    'phone' => $request->phone,
                    'password' => bcrypt(Str::random(12)),
                    'sponsor_id' => 'CLT' . strtoupper(Str::random(6)),
                    'parrain_id' => $sponsor->id,
                    'is_active' => true,
                    'user_type' => 'client',
                    'kyc_status' => 'not_submitted',
                    'pv_balance' => 0,
                    'bv_balance' => 0,
                    'monthly_pv' => 0,
                    'monthly_bv' => 0,
                    'team_pv' => 0,
                    'team_bv' => 0,
                    'total_team' => 0,
                    'address' => $request->address,
                    'city' => $request->city,
                    'country' => $request->country,
                ]);
                
                Wallet::create([
                    'user_id' => $client->id,
                    'balance' => 0,
                    'pending_balance' => 0,
                    'currency' => 'USD',
                    'is_active' => true,
                ]);
            } else {
                if (!$client->parrain_id) {
                    $client->parrain_id = $sponsor->id;
                }
                if ($request->filled('address')) {
                    $client->address = $request->address;
                }
                if ($request->filled('city')) {
                    $client->city = $request->city;
                }
                if ($request->filled('country')) {
                    $client->country = $request->country;
                }
                $client->save();
            }
            
            $subtotal = 0;
            $totalPv = 0;
            $totalBv = 0;
            $productIds = [];
            
            foreach ($cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
                $totalPv += ($item['pv_value'] ?? 0) * $item['quantity'];
                $totalBv += ($item['bv_value'] ?? 0) * $item['quantity'];
                $productIds[] = $item['id'];
            }
            
            $total = $subtotal;
            $commissionAmount = $request->input('commission_amount', 0);
            
            DB::beginTransaction();
            
            try {
                $orderNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                
                $order = Order::create([
                    'user_id' => $client->id,
                    'cashier_id' => auth()->id(),
                    'created_by' => auth()->id(),
                    'order_number' => $orderNumber,
                    'subtotal' => $subtotal,
                    'tax' => 0,
                    'shipping' => 0,
                    'discount' => 0,
                    'total' => $total,
                    'total_pv' => $totalPv,
                    'total_bv' => $totalBv,
                    'status' => 'completed',
                    'payment_status' => 'completed',
                    'payment_method' => 'cash',
                    'source' => 'pos',
                    'shipping_address' => $client->address,
                    'billing_address' => $client->address,
                    'metadata' => [
                        'cashier_id' => auth()->id(),
                        'sponsor_id' => $sponsor->id,
                        'cashier_name' => auth()->user()->name,
                        'pos_sale' => true,
                        'multi_products' => true,
                        'product_count' => count($cart),
                        'commission_amount' => $commissionAmount,
                    ],
                    'paid_at' => now(),
                ]);
                
                foreach ($cart as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['id'],
                        'name' => $item['name'],
                        'sku' => 'PROD-' . $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['price'] * $item['quantity'],
                        'pv_value' => $item['pv_value'] ?? 0,
                        'bv_value' => $item['bv_value'] ?? 0,
                    ]);
                    
                    Product::where('id', $item['id'])->decrement('stock', $item['quantity']);
                }
                
                if ($totalPv > 0 && $sponsor) {
                    $sponsor->increment('team_pv', $totalPv);
                    
                    Log::info('PV ajouté au team_pv du sponsor (multi-produits POS)', [
                        'sponsor_id' => $sponsor->id,
                        'pv_added' => $totalPv,
                        'client_id' => $client->id,
                        'client_name' => $client->name,
                    ]);
                }
                
                if ($commissionAmount > 0 && $commissionAmount >= 5 && $commissionAmount <= 15) {
                    Commission::create([
                        'user_id' => $sponsor->id,
                        'from_user_id' => $client->id,
                        'period' => now()->format('Y-m'),
                        'type' => 'cash_pos',
                        'source' => 'pos',
                        'amount' => $commissionAmount,
                        'percentage' => 0,
                        'description' => "Commission CASH POS - Commande #{$orderNumber}",
                        'notes' => "Montant: $" . number_format($commissionAmount, 2) . " (5$ à 15$) - " . count($cart) . " produits - Payé en espèce",
                        'order_id' => $order->id,
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }
                
                Commission::create([
                    'user_id' => auth()->id(),
                    'from_user_id' => $client->id,
                    'period' => now()->format('Y-m'),
                    'type' => 'pos_transaction',
                    'source' => 'pos',
                    'amount' => 0,
                    'percentage' => 0,
                    'description' => "Vente POS - Commande #{$orderNumber}",
                    'notes' => "Client: {$client->name} - " . count($cart) . " produit(s) - Total: $" . number_format($total, 2),
                    'order_id' => $order->id,
                    'status' => 'completed',
                ]);
                
                if ($client->wasRecentlyCreated) {
                    Commission::create([
                        'user_id' => $client->id,
                        'from_user_id' => $sponsor->id,
                        'period' => now()->format('Y-m'),
                        'type' => 'new_client',
                        'source' => 'pos',
                        'amount' => 0,
                        'percentage' => 0,
                        'description' => "Nouveau client - Parrain: {$sponsor->name}",
                        'notes' => "Code parrain: {$sponsor->sponsor_id}",
                        'order_id' => $order->id,
                        'status' => 'completed',
                    ]);
                }
                
                Commission::create([
                    'user_id' => $client->id,
                    'from_user_id' => $sponsor->id,
                    'period' => now()->format('Y-m'),
                    'type' => 'purchase',
                    'source' => 'pos',
                    'amount' => $total,
                    'percentage' => 0,
                    'description' => "Achat POS - Commande #{$orderNumber}",
                    'notes' => count($cart) . " produit(s) - Total: $" . number_format($total, 2),
                    'order_id' => $order->id,
                    'status' => 'completed',
                ]);
                
                UpdateTeamPV::dispatch($sponsor->id, true);
                $sponsor->calculateAndUpdateRank();
                
                // Sauvegarder les données pour l'impression
                $this->saveOrderData($order, $commissionAmount);
                
                session()->forget('pos_cart');
                
                DB::commit();
                
                return redirect()->route('cashier.orders.invoice', $order->id)
                    ->with('success', 'Vente #' . $orderNumber . ' validée avec ' . count($cart) . ' produits ! Commission CASH POS payée.');
                    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur création commande multi-produits: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur validation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarder les données pour l'impression
     */
    protected function saveOrderData($order, $commissionAmount)
    {
        $data = [
            'date' => now()->format('d/m/Y H:i'),
            'items' => $order->items->map(function($item) {
                return [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            })->toArray(),
            'total' => $order->total,
            'commission' => $commissionAmount ?? 0,
        ];
        
        session(['last_order_data' => $data]);
        return $data;
    }

    /**
     * Vérification du code parrain (AJAX)
     */
    public function checkSponsor(Request $request)
    {
        $code = $request->get('code');
        
        if (!$code) {
            return response()->json([
                'valid' => false,
                'message' => 'Code requis'
            ]);
        }
        
        $sponsor = User::where('sponsor_id', $code)
            ->where('is_active', true)
            ->first();
            
        if ($sponsor) {
            return response()->json([
                'valid' => true,
                'sponsor' => [
                    'id' => $sponsor->id,
                    'name' => $sponsor->name,
                    'email' => $sponsor->email,
                    'phone' => $sponsor->phone,
                    'rank' => $sponsor->rank ?? 'Membre',
                ]
            ]);
        }
        
        return response()->json([
            'valid' => false,
            'message' => 'Code parrain invalide'
        ]);
    }

    /**
     * Ajouter un article au panier (AJAX)
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'type' => 'required|string|in:product,package',
            'quantity' => 'nullable|integer|min:1',
        ]);
        
        $cart = session()->get('pos_cart', []);
        $productId = $request->product_id;
        $type = $request->type;
        $quantity = $request->quantity ?? 1;
        
        $key = $type . '_' . $productId;
        
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            if ($type === 'product') {
                $product = Product::where('is_active', true)->find($productId);
                if (!$product) {
                    return response()->json(['success' => false, 'message' => 'Produit non trouvé'], 404);
                }
                $cart[$key] = [
                    'id' => $product->id,
                    'type' => 'product',
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => $product->image,
                    'source' => 'pos',
                    'source_label' => 'POS',
                    'pv_value' => $product->pv_value ?? 0,
                    'bv_value' => $product->bv_value ?? 0,
                    'quantity' => $quantity,
                ];
            } else {
                $package = Package::where('is_active', true)->find($productId);
                if (!$package) {
                    return response()->json(['success' => false, 'message' => 'Package non trouvé'], 404);
                }
                $cart[$key] = [
                    'id' => $package->id,
                    'type' => 'package',
                    'name' => $package->name,
                    'price' => $package->price,
                    'image' => null,
                    'source' => 'mlm',
                    'source_label' => 'MLM',
                    'pv_value' => $package->pv_value ?? 0,
                    'bv_value' => $package->bv_value ?? 0,
                    'quantity' => $quantity,
                ];
            }
        }
        
        session()->put('pos_cart', $cart);
        
        $totalItems = array_sum(array_column($cart, 'quantity'));
        
        return response()->json([
            'success' => true,
            'message' => 'Article ajouté au panier',
            'total_items' => $totalItems,
            'cart' => $cart,
        ]);
    }

    /**
     * Page de checkout - Récupère les items depuis l'URL ou la session
     */
    public function checkout(Request $request)
    {
        $itemsParam = $request->query('items', '');
        $cartJson = $request->query('cart', '');
        
        Log::info('=== CHECKOUT ===');
        Log::info('itemsParam: ' . $itemsParam);
        
        $cartItems = collect();
        $total = 0;
        $totalPv = 0;
        $totalBv = 0;
        
        if (!empty($itemsParam)) {
            $cart = [];
            $parts = explode(',', $itemsParam);
            
            foreach ($parts as $part) {
                if (empty($part)) continue;
                $data = explode(':', $part);
                if (count($data) !== 2) continue;
                
                list($type, $id) = $data;
                $id = (int)$id;
                
                if ($type === 'product') {
                    $product = Product::where('is_active', true)->find($id);
                    if ($product) {
                        $cart[] = [
                            'id' => $product->id,
                            'type' => 'product',
                            'name' => $product->name,
                            'price' => $product->price,
                            'image' => $product->image ? asset('storage/products/' . $product->image) : null,
                            'source' => 'pos',
                            'source_label' => 'POS',
                            'pv_value' => $product->pv_value ?? 0,
                            'bv_value' => $product->bv_value ?? 0,
                            'quantity' => 1
                        ];
                    }
                } elseif ($type === 'package') {
                    $package = Package::where('is_active', true)->find($id);
                    if ($package) {
                        $cart[] = [
                            'id' => $package->id,
                            'type' => 'package',
                            'name' => $package->name,
                            'price' => $package->price,
                            'image' => null,
                            'source' => 'mlm',
                            'source_label' => 'MLM',
                            'pv_value' => $package->pv_value ?? 0,
                            'bv_value' => $package->bv_value ?? 0,
                            'quantity' => 1
                        ];
                    }
                }
            }
            
            $cartItems = collect($cart);
        }
        
        if ($cartItems->isEmpty()) {
            $cart = session()->get('pos_cart', []);
            if (!empty($cart)) {
                $cartItems = collect($cart);
            }
        }
        
        if ($cartItems->isEmpty() && !empty($cartJson)) {
            try {
                $decoded = json_decode(base64_decode($cartJson), true);
                if (is_array($decoded) && !empty($decoded)) {
                    $cartItems = collect($decoded);
                }
            } catch (\Exception $e) {
                Log::error('Erreur décodage localStorage: ' . $e->getMessage());
            }
        }
        
        $total = $cartItems->sum(function($item) {
            return $item['price'] * $item['quantity'];
        });
        
        $totalPv = $cartItems->sum(function($item) {
            return ($item['pv_value'] ?? 0) * $item['quantity'];
        });
        
        $totalBv = $cartItems->sum(function($item) {
            return ($item['bv_value'] ?? 0) * $item['quantity'];
        });
        
        if ($cartItems->isNotEmpty()) {
            session()->put('pos_cart', $cartItems->toArray());
        }
        
        Log::info('Affichage de la vue checkout avec ' . $cartItems->count() . ' articles');
        
        return view('cashier.checkout', compact('cartItems', 'total', 'totalPv', 'totalBv'));
    }

    /**
 * Créer une commande depuis le checkout (panier multi-produits)
 */
public function createCheckoutOrder(Request $request)
{
    Log::info('=== CREATION COMMANDE CHECKOUT ===');
    Log::info('Données reçues:', $request->all());
    
    try {
        $request->validate([
            'customer_id' => 'nullable|exists:users,id',  
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'customer_phone' => 'nullable|string|max:20',  
            'email' => 'nullable|email|max:255',
            'sponsor_code' => 'required|string|exists:users,sponsor_id',
            'commission_amount' => 'nullable|numeric|min:0|max:15',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);
        
        $cart = session()->get('pos_cart', []);
        
        if (empty($cart)) {
            return redirect()->back()->with('error', 'Votre panier est vide.');
        }
        
        $sponsor = User::where('sponsor_id', $request->sponsor_code)
            ->where('is_active', true)
            ->first();
            
        if (!$sponsor) {
            return redirect()->back()->with('error', 'Code parrain invalide ou inactif');
        }
        
        // ✅ RÉCUPÉRER LE CLIENT - CORRIGÉ
        $client = null;
        
        // 1. D'abord, vérifier si un customer_id est fourni (client sélectionné)
        if ($request->filled('customer_id')) {
            $client = User::find($request->customer_id);
            Log::info('Client trouvé par ID:', ['id' => $client->id ?? null]);
        }
        
        // 2. Sinon, chercher par téléphone
        if (!$client && $request->filled('phone')) {
            $client = User::where('phone', $request->phone)->first();
            Log::info('Client trouvé par téléphone:', ['phone' => $request->phone]);
        }
        
        // 3. Sinon, chercher par customer_phone
        if (!$client && $request->filled('customer_phone')) {
            $client = User::where('phone', $request->customer_phone)->first();
            Log::info('Client trouvé par customer_phone:', ['phone' => $request->customer_phone]);
        }
        
        // 4. Si toujours pas de client, en créer un nouveau
        if (!$client) {
            // Vérifier qu'on a les infos nécessaires
            if (!$request->filled('name') || !$request->filled('phone')) {
                return redirect()->back()->with('error', 'Veuillez remplir le nom et le téléphone du client.');
            }
            
            $client = User::create([
                'name' => $request->name,
                'email' => $request->email ?? $request->phone . '@client.tmp',
                'phone' => $request->phone,
                'password' => bcrypt(Str::random(12)),
                'sponsor_id' => 'CLT' . strtoupper(Str::random(6)),
                'parrain_id' => $sponsor->id,
                'is_active' => true,
                'user_type' => 'client',
                'kyc_status' => 'not_submitted',
                'pv_balance' => 0,
                'bv_balance' => 0,
                'monthly_pv' => 0,
                'monthly_bv' => 0,
                'team_pv' => 0,
                'team_bv' => 0,
                'total_team' => 0,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
            ]);
            
            Wallet::create([
                'user_id' => $client->id,
                'balance' => 0,
                'pending_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);
            
            Log::info('Nouveau client créé:', ['id' => $client->id]);
        } else {
            // ✅ Mettre à jour le client existant (ce bloc était mal placé)
            if (!$client->parrain_id) {
                $client->parrain_id = $sponsor->id;
            }
            if ($request->filled('address')) {
                $client->address = $request->address;
            }
            if ($request->filled('city')) {
                $client->city = $request->city;
            }
            if ($request->filled('country')) {
                $client->country = $request->country;
            }
            $client->save();
            Log::info('Client mis à jour:', ['id' => $client->id]);
        }
        
        $subtotal = 0;
        $totalPv = 0;
        $totalBv = 0;
        $isMlmProduct = false;
        
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $totalPv += ($item['pv_value'] ?? 0) * $item['quantity'];
            $totalBv += ($item['bv_value'] ?? 0) * $item['quantity'];
            
            if (isset($item['source']) && $item['source'] == 'mlm') {
                $isMlmProduct = true;
            }
        }
        
        $total = $subtotal;
        $commissionAmount = $request->input('commission_amount', 0);
        
        DB::beginTransaction();
        
        try {
            $orderNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            
            $order = Order::create([
                'user_id' => $client->id,
                'cashier_id' => auth()->id(),
                'created_by' => auth()->id(),
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'tax' => 0,
                'shipping' => 0,
                'discount' => 0,
                'total' => $total,
                'total_pv' => $totalPv,
                'total_bv' => $totalBv,
                'status' => 'completed',
                'payment_status' => 'completed',
                'payment_method' => 'cash',
                'source' => $isMlmProduct ? 'mlm' : 'pos',
                'shipping_address' => $client->address,
                'billing_address' => $client->address,
                'metadata' => [
                    'cashier_id' => auth()->id(),
                    'sponsor_id' => $sponsor->id,
                    'cashier_name' => auth()->user()->name,
                    'pos_sale' => true,
                    'multi_products' => true,
                    'product_count' => count($cart),
                    'commission_amount' => $commissionAmount,
                ],
                'paid_at' => now(),
            ]);
            
            foreach ($cart as $item) {
                $product = Product::find($item['id']);
                $package = null;
                
                if (!$product) {
                    $package = Package::find($item['id']);
                }
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product ? $product->id : null,
                    'package_id' => $package ? $package->id : null,
                    'name' => $item['name'],
                    'sku' => $product ? 'PROD-' . $product->id : 'PKG-' . $package->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                    'pv_value' => $item['pv_value'] ?? 0,
                    'bv_value' => $item['bv_value'] ?? 0,
                ]);
                
                if ($product) {
                    Product::where('id', $item['id'])->decrement('stock', $item['quantity']);
                }
            }
            
            if ($totalPv > 0 && $sponsor) {
                $sponsor->increment('team_pv', $totalPv);
                
                if ($client->user_type === 'member') {
                    $client->increment('pv_balance', $totalPv);
                    $client->increment('monthly_pv', $totalPv);
                }
            }
            
            if ($commissionAmount > 0 && $commissionAmount >= 5 && $commissionAmount <= 15) {
                Commission::create([
                    'user_id' => $sponsor->id,
                    'from_user_id' => $client->id,
                    'period' => now()->format('Y-m'),
                    'type' => 'cash_pos',
                    'source' => 'pos',
                    'amount' => $commissionAmount,
                    'percentage' => 0,
                    'description' => "Commission CASH POS - Commande #{$orderNumber}",
                    'notes' => "Montant: $" . number_format($commissionAmount, 2) . " (5$ à 15$) - " . count($cart) . " produits - Payé en espèce",
                    'order_id' => $order->id,
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }
            
            Commission::create([
                'user_id' => auth()->id(),
                'from_user_id' => $client->id,
                'period' => now()->format('Y-m'),
                'type' => 'pos_transaction',
                'source' => 'pos',
                'amount' => 0,
                'percentage' => 0,
                'description' => "Vente POS - Commande #{$orderNumber}",
                'notes' => "Client: {$client->name} - " . count($cart) . " produit(s) - Total: $" . number_format($total, 2),
                'order_id' => $order->id,
                'status' => 'completed',
            ]);
            
            if ($client->wasRecentlyCreated) {
                Commission::create([
                    'user_id' => $client->id,
                    'from_user_id' => $sponsor->id,
                    'period' => now()->format('Y-m'),
                    'type' => 'new_client',
                    'source' => 'pos',
                    'amount' => 0,
                    'percentage' => 0,
                    'description' => "Nouveau client - Parrain: {$sponsor->name}",
                    'notes' => "Code parrain: {$sponsor->sponsor_id}",
                    'order_id' => $order->id,
                    'status' => 'completed',
                ]);
            }
            
            Commission::create([
                'user_id' => $client->id,
                'from_user_id' => $sponsor->id,
                'period' => now()->format('Y-m'),
                'type' => 'purchase',
                'source' => 'pos',
                'amount' => $total,
                'percentage' => 0,
                'description' => "Achat POS - Commande #{$orderNumber}",
                'notes' => count($cart) . " produit(s) - Total: $" . number_format($total, 2),
                'order_id' => $order->id,
                'status' => 'completed',
            ]);
            
            UpdateTeamPV::dispatch($sponsor->id, true);
            $sponsor->calculateAndUpdateRank();
            
            // Sauvegarder les données pour l'impression
            $this->saveOrderData($order, $commissionAmount);
            
            session()->forget('pos_cart');
            
            DB::commit();
            
            return redirect()->route('cashier.orders.invoice', $order->id)
                ->with('success', 'Commande #' . $orderNumber . ' validée avec ' . count($cart) . ' produits !')
                ->with('clear_cart', true);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création commande checkout: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
        }
        
    } catch (\Exception $e) {
        Log::error('Erreur validation: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
    }
}
    /**
     * Liste des commandes (POS + En ligne + MLM)
     */
    public function orders(Request $request)
    {
        $query = Order::with(['user', 'cashier']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($sub) use ($search) {
                      $sub->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total' => Order::count(),
            'pos_count' => Order::where('source', 'pos')->count(),
            'web_count' => Order::whereIn('source', ['web', 'online'])->count(),
            'mlm_count' => Order::where('source', 'mlm')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];
        
        return view('cashier.orders', compact('orders', 'stats'));
    }

    /**
     * Détails d'une commande
     */
    public function showOrder($id)
    {
        $order = Order::with(['user', 'cashier', 'items'])
            ->where(function($query) use ($id) {
                $query->where('id', $id)
                      ->orWhere('order_number', $id);
            })
            ->firstOrFail();
        
        return view('cashier.order-detail', compact('order'));
    }

    /**
 * Facture d'une commande
 */
public function invoice($id)
{
    // ✅ Charger toutes les relations nécessaires
    $order = Order::with(['user', 'cashier', 'items', 'items.product'])
        ->where(function($query) use ($id) {
            $query->where('id', $id)
                  ->orWhere('order_number', $id);
        })
        ->firstOrFail();
    
    // ✅ Récupérer le sponsor depuis le metadata
    $sponsor = null;
    if (isset($order->metadata['sponsor_id'])) {
        $sponsor = User::find($order->metadata['sponsor_id']);
    }
    
    // ✅ Récupérer le caissier
    $cashier = null;
    if (isset($order->metadata['cashier_id'])) {
        $cashier = User::find($order->metadata['cashier_id']);
    }
    
    return view('cashier.invoice', compact('order', 'sponsor', 'cashier'));
}

    /**
     * Imprimer la facture
     */
    public function printInvoice($id)
    {
        $order = Order::with(['user', 'cashier', 'items'])
            ->where(function($query) use ($id) {
                $query->where('id', $id)
                      ->orWhere('order_number', $id);
            })
            ->firstOrFail();
        
        $sponsor = null;
        if (isset($order->metadata['sponsor_id'])) {
            $sponsor = User::find($order->metadata['sponsor_id']);
        }
        
        return view('cashier.invoice-print', compact('order', 'sponsor'));
    }

    /**
     * Télécharger la facture en PDF
     */
    public function downloadInvoice($id)
    {
        $order = Order::with(['user', 'cashier', 'items'])
            ->where(function($query) use ($id) {
                $query->where('id', $id)
                      ->orWhere('order_number', $id);
            })
            ->firstOrFail();
        
        $sponsor = null;
        if (isset($order->metadata['sponsor_id'])) {
            $sponsor = User::find($order->metadata['sponsor_id']);
        }
        
        $pdf = Pdf::loadView('cashier.invoice-pdf', compact('order', 'sponsor'));
        return $pdf->download('facture_' . $order->order_number . '.pdf');
    }

    /**
     * Annuler une commande
     */
    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);
        
        if ($order->status === 'cancelled') {
            return redirect()->back()->with('error', 'Cette commande est déjà annulée.');
        }
        
        if ($order->status === 'completed') {
            return redirect()->back()->with('error', 'Impossible d\'annuler une commande déjà complétée.');
        }
        
        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => auth()->id(),
        ]);
        
        return redirect()->back()->with('success', 'Commande annulée avec succès.');
    }

    /**
     * Liste des clients
     */
    public function customers(Request $request)
    {
        $query = User::where('user_type', 'client');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('sponsor_id', 'LIKE', "%{$search}%");
            });
        }
        
        $customers = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('cashier.customers', compact('customers'));
    }

    /**
     * Rechercher un client (AJAX)
     */
    public function searchCustomer(Request $request)
    {
        $search = $request->get('q');

        $customers = User::where('is_active', true)
            ->where('user_type', 'client')
            ->where(function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('sponsor_id', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone', 'sponsor_id']);

        return response()->json($customers);
    }

    /**
     * Détails d'un client
     */
    public function showCustomer($id)
    {
        $customer = User::with(['orders', 'commissions'])->where('user_type', 'client')->findOrFail($id);
        
        $stats = [
            'total_orders' => $customer->orders->count(),
            'total_spent' => $customer->orders->sum('total'),
            'total_commissions' => $customer->commissions->where('status', 'paid')->sum('amount'),
        ];
        
        return view('cashier.customers.show', compact('customer', 'stats'));
    }

    /**
     * Créer un client (AJAX)
     */
    public function createCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone ?? 'N/A',
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'password' => Hash::make(Str::random(12)),
                'is_active' => true,
                'user_type' => 'client',
                'sponsor_id' => 'CLT' . strtoupper(Str::random(6)),
                'kyc_status' => 'not_submitted',
                'pv_balance' => 0,
                'bv_balance' => 0,
                'monthly_pv' => 0,
                'monthly_bv' => 0,
                'team_pv' => 0,
                'team_bv' => 0,
                'total_team' => 0,
            ]);

            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Client créé avec succès',
                'customer' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'sponsor_id' => $user->sponsor_id,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Générer un code sponsor unique
     */
    private function generateSponsorId(): string
    {
        $prefix = 'CLT';
        do {
            $random = strtoupper(Str::random(6));
            $code = $prefix . $random;
        } while (User::where('sponsor_id', $code)->exists());
        
        return $code;
    }

    /**
     * Ventes du jour
     */
    public function dailySales()
    {
        $sales = Order::whereDate('created_at', today())
            ->where('status', 'completed')
            ->with('user')
            ->get();

        $stats = [
            'total_orders' => $sales->count(),
            'total_amount' => $sales->sum('total'),
            'average_order' => $sales->count() > 0 ? $sales->sum('total') / $sales->count() : 0,
            'pos_count' => $sales->where('source', 'pos')->count(),
            'mlm_count' => $sales->where('source', 'mlm')->count(),
            'payment_methods' => $sales->groupBy('payment_method')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('total'),
                ];
            }),
        ];

        return view('cashier.daily-sales', compact('sales', 'stats'));
    }

    /**
     * Historique des ventes POS
     */
    public function history(Request $request)
    {
        $query = Order::with(['user', 'cashier', 'items'])
            ->where('source', 'pos')
            ->where('status', 'completed');
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($sub) use ($search) {
                      $sub->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $allOrdersQuery = Order::with(['items'])
            ->where('source', 'pos')
            ->where('status', 'completed');
        
        if ($request->filled('date_from')) {
            $allOrdersQuery->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $allOrdersQuery->whereDate('created_at', '<=', $request->date_to);
        }
        
        $allOrders = $allOrdersQuery->get();
        $totalPvAll = 0;
        foreach ($allOrders as $order) {
            $totalPvAll += $order->items->sum(function($item) {
                return ($item->pv_value ?? 0) * $item->quantity;
            });
        }
        
        $stats = [
            'total_orders' => Order::where('source', 'pos')->where('status', 'completed')->count(),
            'total_sales' => Order::where('source', 'pos')->where('status', 'completed')->sum('total'),
            'total_pv' => $totalPvAll,
            'total_commissions' => Commission::where('source', 'pos')
                ->where('status', 'paid')
                ->where('type', 'cash_pos')
                ->sum('amount'),
        ];
        
        return view('cashier.history', compact('orders', 'stats'));
    }

    // ============================================================
    // GESTION DES MEMBRES
    // ============================================================

    /**
     * Liste des membres (UNIQUEMENT user_type = 'member')
     */
    public function members(Request $request)
    {
        $query = User::where('user_type', 'member');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('sponsor_id', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->status == 'active') {
            $query->where('is_active', true);
        } elseif ($request->status == 'inactive') {
            $query->where('is_active', false);
        }
        
        if ($request->role) {
            $query->role($request->role);
        }
        
        $members = $query->with('package')->paginate(20);
        
        $stats = [
            'total' => User::where('user_type', 'member')->count(),
            'active' => User::where('user_type', 'member')->where('is_active', true)->count(),
            'with_commissions' => Commission::where('source', 'pos')->where('status', 'paid')->distinct('user_id')->count('user_id'),
            'orders' => Order::where('source', 'pos')->count(),
        ];
        
        return view('cashier.members', compact('members', 'stats'));
    }

    /**
     * Détails d'un membre
     */
    public function memberShow($id)
    {
        $member = User::where('user_type', 'member')->with(['package', 'orders'])->findOrFail($id);
        
        $commissions = Commission::where('user_id', $member->id)
            ->where('source', 'pos')
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $stats = [
            'total_commissions' => Commission::where('user_id', $member->id)->where('source', 'pos')->where('status', 'paid')->sum('amount'),
            'paid_commissions' => Commission::where('user_id', $member->id)->where('source', 'pos')->where('status', 'paid')->sum('amount'),
            'pending_commissions' => Commission::where('user_id', $member->id)->where('source', 'pos')->where('status', 'pending')->sum('amount'),
            'approved_commissions' => Commission::where('user_id', $member->id)->where('source', 'pos')->where('status', 'approved')->count(),
            'cancelled_commissions' => Commission::where('user_id', $member->id)->where('source', 'pos')->where('status', 'cancelled')->count(),
        ];
        
        return view('cashier.members.show', compact('member', 'commissions', 'stats'));
    }

    /**
     * Commandes d'un membre
     */
    public function memberOrders($id)
    {
        $member = User::where('user_type', 'member')->findOrFail($id);
        $orders = Order::where('user_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('cashier.members.orders', compact('member', 'orders'));
    }

    /**
     * Commissions d'un membre (UNIQUEMENT POS)
     */
    public function memberCommissions($id)
    {
        $member = User::where('user_type', 'member')->findOrFail($id);
        
        $commissions = Commission::where('user_id', $member->id)
            ->where('source', 'pos')
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $stats = [
            'total' => Commission::where('user_id', $member->id)->where('source', 'pos')->where('status', 'paid')->sum('amount'),
            'paid' => Commission::where('user_id', $member->id)->where('source', 'pos')->where('status', 'paid')->sum('amount'),
            'pending' => Commission::where('user_id', $member->id)->where('source', 'pos')->where('status', 'pending')->sum('amount'),
            'approved' => Commission::where('user_id', $member->id)->where('source', 'pos')->where('status', 'approved')->count(),
        ];
        
        return view('cashier.members.commissions', compact('member', 'commissions', 'stats'));
    }

    /**
     * Mettre à jour les commissions d'un membre
     */
    public function updateMemberCommissions(Request $request, $id)
    {
        $member = User::where('user_type', 'member')->findOrFail($id);
        
        $request->validate([
            'commission_amount' => 'required|numeric|min:0',
            'commission_type' => 'required|string|in:bonus,commission,cash_pos',
            'description' => 'nullable|string|max:255',
        ]);
        
        try {
            DB::beginTransaction();
            
            $commission = Commission::create([
                'user_id' => $member->id,
                'from_user_id' => auth()->id(),
                'period' => now()->format('Y-m'),
                'type' => $request->commission_type,
                'source' => 'manual',
                'amount' => $request->commission_amount,
                'percentage' => 0,
                'description' => $request->description ?? "Commission manuelle - " . $request->commission_type,
                'notes' => "Ajoutée par le caissier: " . auth()->user()->name,
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $member->id],
                [
                    'balance' => 0,
                    'pending_balance' => 0,
                    'currency' => 'USD',
                    'is_active' => true,
                ]
            );
            $wallet->increment('balance', $request->commission_amount);
            
            Transaction::create([
                'user_id' => $member->id,
                'type' => 'commission',
                'amount' => $request->commission_amount,
                'status' => 'completed',
                'description' => $request->description ?? "Commission manuelle",
                'reference' => 'MAN-' . Str::upper(Str::random(8)),
                'metadata' => [
                    'commission_id' => $commission->id,
                    'cashier_id' => auth()->id(),
                    'cashier_name' => auth()->user()->name,
                ],
            ]);
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Commission ajoutée avec succès.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur updateMemberCommissions: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Payer toutes les commissions en attente d'un membre
     */
    public function payAllMemberCommissions($id)
    {
        $member = User::where('user_type', 'member')->findOrFail($id);
        
        $pendingCommissions = Commission::where('user_id', $member->id)
            ->where('source', 'pos')
            ->where('status', 'pending')
            ->get();
        
        if ($pendingCommissions->isEmpty()) {
            return redirect()->back()->with('info', 'Aucune commission en attente pour ce membre.');
        }
        
        try {
            DB::beginTransaction();
            
            $totalAmount = $pendingCommissions->sum('amount');
            
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $member->id],
                [
                    'balance' => 0,
                    'pending_balance' => 0,
                    'currency' => 'USD',
                    'is_active' => true,
                ]
            );
            $wallet->increment('balance', $totalAmount);
            
            foreach ($pendingCommissions as $commission) {
                $commission->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }
            
            Transaction::create([
                'user_id' => $member->id,
                'type' => 'commission_batch',
                'amount' => $totalAmount,
                'status' => 'completed',
                'description' => 'Paiement de ' . $pendingCommissions->count() . ' commissions en attente',
                'reference' => 'BATCH-' . Str::upper(Str::random(8)),
                'metadata' => [
                    'commission_count' => $pendingCommissions->count(),
                    'cashier_id' => auth()->id(),
                    'cashier_name' => auth()->user()->name,
                ],
            ]);
            
            DB::commit();
            
            return redirect()->back()->with('success', $pendingCommissions->count() . ' commissions payées avec succès. Total: $' . number_format($totalAmount, 2));
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur payAllMemberCommissions: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    // ============================================================
    // COMMISSIONS (UNIQUEMENT POS)
    // ============================================================

    /**
     * Liste toutes les commissions POS
     */
    public function commissions(Request $request)
    {
        $excludedTypes = ['pos_transaction', 'purchase', 'new_client'];
        
        $query = Commission::with(['user', 'fromUser', 'order'])
            ->whereNotIn('type', $excludedTypes);
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total_commissions' => Commission::whereNotIn('type', $excludedTypes)->sum('amount'),
            'paid_commissions' => Commission::whereNotIn('type', $excludedTypes)->where('status', 'paid')->sum('amount'),
            'pending_commissions' => Commission::whereNotIn('type', $excludedTypes)->where('status', 'pending')->sum('amount'),
            'approved_commissions' => Commission::whereNotIn('type', $excludedTypes)->where('status', 'approved')->sum('amount'),
            'total_members' => Commission::whereNotIn('type', $excludedTypes)->distinct('user_id')->count('user_id'),
            'pos_total' => Commission::where('source', 'pos')->whereNotIn('type', $excludedTypes)->sum('amount'),
            'mlm_total' => Commission::where('source', 'mlm')->whereNotIn('type', $excludedTypes)->sum('amount'),
        ];
        
        $members = User::whereHas('commissions', function($q) use ($excludedTypes) {
            $q->whereNotIn('type', $excludedTypes);
        })->get(['id', 'name', 'sponsor_id']);
        
        $types = Commission::whereNotIn('type', $excludedTypes)->distinct()->pluck('type');
        $sources = ['pos' => 'POS', 'mlm' => 'MLM'];
        $statuses = ['pending' => 'En attente', 'approved' => 'Approuvée', 'paid' => 'Payée', 'rejected' => 'Rejetée', 'cancelled' => 'Annulée'];
        
        return view('cashier.commission-fixed', compact(
            'commissions', 
            'stats', 
            'members', 
            'types', 
            'sources', 
            'statuses'
        ));
    }

    /**
     * Détails d'une commission
     */
    public function commissionShow($id)
    {
        $commission = Commission::with(['user', 'fromUser', 'order'])
            ->findOrFail($id);

        $parrain = User::find($commission->user->parrain_id ?? null);

        $similarCommissions = Commission::where('user_id', $commission->user_id)
            ->where('id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $networkCommissions = Commission::where('user_id', $commission->user_id)
            ->where('status', 'paid')
            ->sum('amount');

        return view('cashier.commissions-show', compact(
            'commission',
            'parrain',
            'similarCommissions',
            'networkCommissions'
        ));
    }

    /**
     * Approuver une commission
     */
    public function approveCommission($id)
    {
        $commission = Commission::findOrFail($id);
        
        if ($commission->status !== 'pending') {
            return redirect()->back()->with('error', 'Cette commission ne peut pas être approuvée.');
        }
        
        $commission->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);
        
        return redirect()->back()->with('success', 'Commission approuvée avec succès.');
    }

    /**
     * Rejeter une commission
     */
    public function rejectCommission($id)
    {
        $commission = Commission::findOrFail($id);
        
        if ($commission->status !== 'pending') {
            return redirect()->back()->with('error', 'Cette commission ne peut pas être rejetée.');
        }
        
        $commission->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
        ]);
        
        return redirect()->back()->with('success', 'Commission rejetée.');
    }

    /**
     * Payer une commission
     */
    public function commissionPay($id)
    {
        $commission = Commission::findOrFail($id);
        
        if (!in_array($commission->status, ['pending', 'approved'])) {
            return redirect()->back()->with('error', 'Cette commission ne peut pas être payée.');
        }
        
        try {
            DB::beginTransaction();
            
            $commission->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $commission->user_id],
                [
                    'balance' => 0,
                    'pending_balance' => 0,
                    'currency' => 'USD',
                    'is_active' => true,
                ]
            );
            $wallet->increment('balance', $commission->amount);
            
            Transaction::create([
                'user_id' => $commission->user_id,
                'type' => 'commission',
                'amount' => $commission->amount,
                'status' => 'completed',
                'description' => 'Paiement de commission: ' . $commission->description,
                'reference' => 'COM-' . Str::upper(Str::random(8)),
                'metadata' => [
                    'commission_id' => $commission->id,
                    'cashier_id' => auth()->id(),
                ],
            ]);
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Commission payée avec succès.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur commissionPay: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Annuler une commission
     */
    public function commissionCancel($id)
    {
        $commission = Commission::findOrFail($id);
        
        if (!in_array($commission->status, ['pending', 'approved'])) {
            return redirect()->back()->with('error', 'Cette commission ne peut pas être annulée.');
        }
        
        $commission->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => auth()->id(),
        ]);
        
        return redirect()->back()->with('success', 'Commission annulée.');
    }

    /**
     * Approuver plusieurs commissions en batch
     */
    public function batchApproveCommissions(Request $request)
    {
        $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'exists:commissions,id',
        ]);
        
        $count = 0;
        $errors = 0;
        
        foreach ($request->commission_ids as $id) {
            $commission = Commission::find($id);
            if ($commission && $commission->status === 'pending') {
                $commission->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                ]);
                $count++;
            } else {
                $errors++;
            }
        }
        
        $message = $count . ' commission(s) approuvée(s).';
        if ($errors > 0) {
            $message .= ' ' . $errors . ' commission(s) ignorée(s).';
        }
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Exporter les commissions en CSV
     */
    public function exportCommissions(Request $request)
    {
        $excludedTypes = ['pos_transaction', 'purchase', 'new_client'];
        
        $query = Commission::with(['user', 'fromUser'])
            ->whereNotIn('type', $excludedTypes);
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        
        $commissions = $query->orderBy('created_at', 'desc')->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="commissions_' . date('Y-m-d') . '.csv"',
        ];
        
        $callback = function() use ($commissions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            fputcsv($file, [
                'ID', 'Membre', 'Email', 'Code Parrain', 'Type', 'Source',
                'Montant', 'Statut', 'Période', 'Crée le', 'Payé le', 'Description'
            ]);
            
            foreach ($commissions as $commission) {
                fputcsv($file, [
                    $commission->id,
                    $commission->user->name ?? 'N/A',
                    $commission->user->email ?? 'N/A',
                    $commission->user->sponsor_id ?? 'N/A',
                    $commission->type,
                    $commission->source,
                    number_format($commission->amount, 2),
                    $commission->status,
                    $commission->period,
                    $commission->created_at->format('Y-m-d H:i'),
                    $commission->paid_at ? $commission->paid_at->format('Y-m-d H:i') : 'N/A',
                    $commission->description,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Statistiques des commissions
     */
    public function commissionsStats()
    {
        $excludedTypes = ['pos_transaction', 'purchase', 'new_client'];
        
        $stats = [
            'total' => Commission::whereNotIn('type', $excludedTypes)->sum('amount'),
            'paid' => Commission::whereNotIn('type', $excludedTypes)->where('status', 'paid')->sum('amount'),
            'pending' => Commission::whereNotIn('type', $excludedTypes)->where('status', 'pending')->sum('amount'),
            'approved' => Commission::whereNotIn('type', $excludedTypes)->where('status', 'approved')->sum('amount'),
            'rejected' => Commission::whereNotIn('type', $excludedTypes)->where('status', 'rejected')->sum('amount'),
            'cancelled' => Commission::whereNotIn('type', $excludedTypes)->where('status', 'cancelled')->sum('amount'),
            'count' => Commission::whereNotIn('type', $excludedTypes)->count(),
            'members' => Commission::whereNotIn('type', $excludedTypes)->distinct('user_id')->count('user_id'),
        ];
        
        $monthlyData = Commission::whereNotIn('type', $excludedTypes)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();
        
        $typeData = Commission::whereNotIn('type', $excludedTypes)
            ->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get();
        
        $topMembers = Commission::whereNotIn('type', $excludedTypes)
            ->selectRaw('user_id, SUM(amount) as total')
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->with('user')
            ->get();
        
        return view('cashier.commissions-stats', compact(
            'stats',
            'monthlyData',
            'typeData',
            'topMembers'
        ));
    }

    /**
     * Voir le réseau d'un membre
     */
    public function viewNetwork($userId)
    {
        $member = User::where('user_type', 'member')->findOrFail($userId);
        
        $directChildren = User::where('parrain_id', $member->id)
            ->where('user_type', 'member')
            ->get();
        $network = $this->buildNetworkTree($member);
        
        $stats = [
            'direct_children' => $directChildren->count(),
            'total_network' => $this->countNetworkMembers($member->id),
            'total_pv' => $member->team_pv ?? 0,
            'total_bv' => $member->team_bv ?? 0,
        ];
        
        return view('cashier.commissions-network', compact('member', 'network', 'stats', 'directChildren'));
    }

    /**
     * Construire l'arbre du réseau (récursif)
     */
    private function buildNetworkTree($user, $level = 0, $maxLevel = 3)
    {
        if ($level > $maxLevel) {
            return null;
        }
        
        $children = User::where('parrain_id', $user->id)
            ->where('user_type', 'member')
            ->get();
        
        $tree = [
            'user' => $user,
            'level' => $level,
            'children' => [],
        ];
        
        foreach ($children as $child) {
            $tree['children'][] = $this->buildNetworkTree($child, $level + 1, $maxLevel);
        }
        
        return $tree;
    }

    /**
     * Compter le nombre de membres dans le réseau (récursif)
     */
    private function countNetworkMembers($userId)
    {
        $count = 0;
        $children = User::where('parrain_id', $userId)
            ->where('user_type', 'member')
            ->get();
        
        foreach ($children as $child) {
            $count++;
            $count += $this->countNetworkMembers($child->id);
        }
        
        return $count;
    }

    // ============================================================
    // PROFIL ET STATISTIQUES
    // ============================================================

    /**
     * Profil du caissier
     */
    public function profile()
    {
        $user = auth()->user();
        return view('cashier.profile', compact('user'));
    }

    /**
     * Mettre à jour le profil
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);
        
        $user->update($request->only(['name', 'phone', 'address', 'city', 'country']));
        
        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Mettre à jour le mot de passe du caissier
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }
        
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        
        return back()->with('success', 'Mot de passe mis à jour avec succès.');
    }

    /**
     * Supprimer le compte du caissier
     */
    public function destroyProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'password' => 'required|string',
        ]);
        
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Le mot de passe est incorrect.']);
        }
        
        auth()->logout();
        $user->delete();
        
        return redirect('/')->with('success', 'Votre compte a été supprimé.');
    }

    /**
     * Mettre à jour l'avatar du caissier
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048|mimes:jpeg,png,jpg,gif',
        ]);
        
        $user = auth()->user();
        
        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $avatar->getClientOriginalExtension();
            $path = $avatar->storeAs('avatars', $filename, 'public');
            
            if ($user->avatar && file_exists(storage_path('app/public/avatars/' . $user->avatar))) {
                unlink(storage_path('app/public/avatars/' . $user->avatar));
            }
            
            $user->update(['avatar' => $filename]);
            
            return response()->json(['success' => true, 'message' => 'Avatar mis à jour.']);
        }
        
        return response()->json(['success' => false, 'message' => 'Aucun fichier téléchargé.'], 400);
    }

    /**
     * Supprimer l'avatar du caissier
     */
    public function deleteAvatar(Request $request)
    {
        $user = auth()->user();
        
        if ($user->avatar && file_exists(storage_path('app/public/avatars/' . $user->avatar))) {
            unlink(storage_path('app/public/avatars/' . $user->avatar));
        }
        
        $user->update(['avatar' => null]);
        
        return response()->json(['success' => true, 'message' => 'Avatar supprimé.']);
    }

    /**
     * Statistiques complètes
     */
    public function stats()
    {
        $stats = [
            'total_orders' => Order::where('source', 'pos')->count(),
            'total_sales' => Order::where('source', 'pos')->sum('total'),
            'total_commissions' => Commission::where('source', 'pos')->where('status', 'paid')->sum('amount'),
            'total_pv' => Order::where('source', 'pos')->sum('total_pv'),
            'total_customers' => User::where('user_type', 'client')->count(),
            'total_members' => User::where('user_type', 'member')->count(),
        ];
        
        $salesByMonth = Order::where('source', 'pos')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();
        
        return view('cashier.stats', compact('stats', 'salesByMonth'));
    }

    /**
     * Exporter les statistiques
     */
    public function exportStats(Request $request)
    {
        $stats = [
            'total_orders' => Order::where('source', 'pos')->count(),
            'total_sales' => Order::where('source', 'pos')->sum('total'),
            'total_commissions' => Commission::where('source', 'pos')->where('status', 'paid')->sum('amount'),
            'total_users' => User::where('user_type', 'member')->count(),
            'total_customers' => User::where('user_type', 'client')->count(),
        ];
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="stats_pos_' . date('Y-m-d') . '.csv"',
        ];
        
        $callback = function() use ($stats) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Métrique', 'Valeur']);
            
            foreach ($stats as $key => $value) {
                fputcsv($file, [str_replace('_', ' ', ucfirst($key)), $value]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

 /**
 * Demander l'annulation d'une commande
 */
public function requestCancellation(Request $request, $id)
{
    $order = Order::findOrFail($id);
    
    // ✅ MODIFICATION : Permettre l'annulation pour les commandes terminées aussi (dans les 10 min)
    if (!in_array($order->status, ['pending', 'processing', 'completed'])) {
        return response()->json([
            'success' => false,
            'message' => 'Cette commande ne peut pas être annulée. Statut: ' . $order->status
        ], 400);
    }
    
    // ✅ VÉRIFIER LE DÉLAI DE 10 MINUTES
    $createdAt = $order->created_at;
    $now = now();
    $diffInMinutes = $createdAt->diffInMinutes($now);
    
    if ($diffInMinutes > 10) {
        return response()->json([
            'success' => false,
            'message' => 'Le délai de 10 minutes pour annuler cette commande est dépassé. (Créée il y a ' . $diffInMinutes . ' minutes)'
        ], 400);
    }
    
    // Vérifier s'il y a déjà une demande en attente
    if (isset($order->metadata['cancellation_request']) && 
        $order->metadata['cancellation_request']['status'] == 'pending') {
        return response()->json([
            'success' => false,
            'message' => 'Une demande d\'annulation est déjà en attente.'
        ], 400);
    }
    
    $request->validate([
        'reason' => 'required|string|min:10|max:500'
    ]);
    
    // Créer la demande d'annulation
    $metadata = $order->metadata ?? [];
    $metadata['cancellation_request'] = [
        'status' => 'pending',
        'reason' => $request->reason,
        'requested_by' => auth()->id(),
        'requested_at' => now()->toISOString(),
    ];
    
    $order->metadata = $metadata;
    $order->save();
    
    Log::info('Demande d\'annulation créée', [
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'user_id' => auth()->id(),
        'reason' => $request->reason,
        'time_elapsed' => $diffInMinutes . ' minutes',
        'status' => $order->status
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Demande d\'annulation envoyée avec succès.'
    ]);
}
    /**
     * Exporter les commissions en PDF - Un membre par ligne
     */
    public function exportPdf(Request $request)
    {
        $excludedTypes = ['pos_transaction', 'purchase', 'new_client'];
        
        $query = Commission::with(['user', 'fromUser'])
            ->whereNotIn('type', $excludedTypes);
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }
        
        if (!$request->filled('period') && !$request->filled('date_from') && !$request->filled('date_to')) {
            $period = date('Y-m');
            $query->where('period', $period);
        } else {
            $period = $request->period ?? date('Y-m');
        }
        
        $commissions = $query->orderBy('created_at', 'desc')->get();
        
        $members = [];
        foreach ($commissions as $commission) {
            $userId = $commission->user_id;
            
            if (!isset($members[$userId])) {
                $members[$userId] = [
                    'user' => $commission->user,
                    'period' => $commission->period ?? $period,
                    'monthly_pv' => $commission->user?->monthly_pv ?? 0,
                    'sponsor' => 0,
                    'direct' => 0,
                    'indirect' => 0,
                    'leadership' => 0,
                    'cash_pos' => 0,
                ];
            }
            
            switch ($commission->type) {
                case 'sponsor':
                    $members[$userId]['sponsor'] += $commission->amount;
                    break;
                case 'direct':
                    $members[$userId]['direct'] += $commission->amount;
                    break;
                case 'indirect':
                    $members[$userId]['indirect'] += $commission->amount;
                    break;
                case 'leadership':
                    $members[$userId]['leadership'] += $commission->amount;
                    break;
                case 'cash_pos':
                    $members[$userId]['cash_pos'] += $commission->amount;
                    break;
            }
        }
        
        $members = collect($members);
        
        $totals = [
            'sponsor' => $members->sum('sponsor'),
            'direct' => $members->sum('direct'),
            'indirect' => $members->sum('indirect'),
            'leadership' => $members->sum('leadership'),
            'cash_pos' => $members->sum('cash_pos'),
            'grand_total' => $members->sum(function($item) {
                return $item['sponsor'] + $item['direct'] + $item['indirect'] + $item['leadership'] + $item['cash_pos'];
            }),
        ];
        
        $logoBase64 = $this->getLogoBase64();
        
        if ($request->has('download')) {
            $pdf = Pdf::loadView('cashier.commissions-pdf', [
                'members' => $members,
                'period' => $period,
                'date' => now(),
                'totals' => $totals,
                'request' => $request,
                'logoBase64' => $logoBase64,
            ]);
            return $pdf->download('commissions_' . $period . '.pdf');
        }
        
        return view('cashier.commissions-pdf', [
            'members' => $members,
            'period' => $period,
            'date' => now(),
            'totals' => $totals,
            'request' => $request,
            'logoBase64' => $logoBase64,
        ]);
    }

    /**
     * ✅ Méthode pour récupérer le logo en base64
     */
    private function getLogoBase64()
    {
        $logoPath = public_path('images/salang_logo.png');
        
        if (file_exists($logoPath)) {
            $imageData = file_get_contents($logoPath);
            $base64 = base64_encode($imageData);
            return 'data:image/png;base64,' . $base64;
        }
        
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
    }
}