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
use App\Jobs\UpdateTeamPV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

    public function dashboard()
    {
        $stats = [
            'total_orders_today' => Order::whereDate('created_at', today())->where('source', 'pos')->count(),
            'total_sales_today' => Order::whereDate('created_at', today())->where('source', 'pos')->sum('total'),
            'customers_today' => User::whereDate('created_at', today())->count(),
            'pending_orders' => Order::where('status', 'pending')->where('source', 'pos')->count(),
        ];

        $recentOrders = Order::with(['user'])
            ->where('source', 'pos')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('cashier.dashboard', compact('stats', 'recentOrders'));
    }

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
            ]);
            
            // 1. Trouver le parrain
            $sponsor = User::where('sponsor_id', $request->sponsor_code)
                ->where('is_active', true)
                ->first();
                
            if (!$sponsor) {
                return redirect()->back()->with('error', 'Code parrain invalide ou inactif');
            }
            
            // 2. Creer ou recuperer le client
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
                ]);
                
                Wallet::create([
                    'user_id' => $client->id,
                    'balance' => 0,
                    'pending_balance' => 0,
                    'currency' => 'USD',
                    'is_active' => true,
                ]);
                
                $client->assignRole('user');
            } else {
                if (!$client->parrain_id) {
                    $client->parrain_id = $sponsor->id;
                    $client->save();
                }
            }
            
            // 3. Recuperer le produit
            $product = Product::find($request->product_id);
            if (!$product) {
                return redirect()->back()->with('error', 'Produit non trouve');
            }
            
            $quantity = $request->input('quantity', 1);
            $subtotal = $product->price * $quantity;
            $total = $subtotal;
            $totalPv = ($product->pv_value ?? 0) * $quantity;
            $totalBv = ($product->bv_value ?? 0) * $quantity;
            $commissionAmount = $request->input('commission_amount', 0);
            
            DB::beginTransaction();
            
            try {
                // 4. Creer la commande
                $orderNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                
                $order = Order::create([
                    'user_id' => $client->id,
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
                
                // 5. Creer l'article
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
                
                // 6. Crediter les PV au parrain
                if ($totalPv > 0) {
                    $sponsor->addPV($totalPv, 'pos_sale', $order->id);
                }
                
                // 7. Creer la commission CASH (5$ à 15$)
                if ($commissionAmount > 0 && $commissionAmount >= 5 && $commissionAmount <= 15) {
                    Commission::create([
                        'user_id' => $sponsor->id,
                        'from_user_id' => $client->id,
                        'period' => now()->format('Y-m'),
                        'type' => 'direct',
                        'source' => 'pos',
                        'amount' => $commissionAmount,
                        'percentage' => 0,
                        'description' => "Commission CASH sur vente POS - Commande #{$orderNumber}",
                        'notes' => "Montant: $" . number_format($commissionAmount, 2) . " (5$ à 15$)",
                        'order_id' => $order->id,
                        'status' => 'pending',
                    ]);
                }
                
                // 8. ACTIVITÉ POUR LE CAISSIER
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
                
                // 9. ACTIVITÉ POUR LE NOUVEAU CLIENT (si nouveau)
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
                
                // 10. ACTIVITÉ POUR L'ACHAT DU CLIENT
                Commission::create([
                    'user_id' => $client->id,
                    'from_user_id' => $sponsor->id,
                    'period' => now()->format('Y-m'),
                    'type' => 'purchase',
                    'source' => 'pos',
                    'amount' => $total,
                    'percentage' => 0,
                    'description' => "🛒 Achat POS - Commande #{$orderNumber}",
                    'notes' => "Produit: {$product->name} x{$quantity} - Total: $" . number_format($total, 2),
                    'order_id' => $order->id,
                    'status' => 'completed',
                ]);
                
                // 11. Mettre a jour le team PV
                UpdateTeamPV::dispatch($sponsor->id, true);
                
                // 12. Recalculer le grade
                $sponsor->calculateAndUpdateRank();
                
                DB::commit();
                
                return redirect()->route('cashier.orders.invoice', $order->id)
                    ->with('success', 'Vente #' . $orderNumber . ' validée avec succès !');
                    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur creation commande POS: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur validation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    // ============================================================
    // METHODES POUR LE PANIER MULTI-PRODUITS AVEC COMMISSION
    // ============================================================

    /**
     * Ajouter un produit au panier (AJAX)
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);
        
        $product = Product::find($request->product_id);
        
        if (!$product || $product->stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non disponible'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image,
            ]
        ]);
    }

    /**
     * Page de checkout multi-produits avec commission
     */
    public function checkout(Request $request)
    {
        $productIds = explode(',', $request->query('products', ''));
        $products = Product::whereIn('id', $productIds)->where('is_active', 1)->get();
        
        if ($products->isEmpty()) {
            return redirect()->route('cashier.pos')->with('error', 'Aucun produit sélectionné');
        }
        
        $total = $products->sum('price');
        
        return view('cashier.checkout', compact('products', 'total'));
    }

    /**
     * Créer une commande multi-produits avec commission CASH
     */
    public function createMultiOrder(Request $request)
    {
        Log::info('=== CREATION COMMANDE POS (MULTI-PRODUITS) ===');
        
        try {
            $request->validate([
                'products' => 'required|string',
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'sponsor_code' => 'required|string|exists:users,sponsor_id',
                'commission_amount' => 'nullable|numeric|min:0|max:15',
            ]);
            
            $productIds = explode(',', $request->products);
            
            // 1. Trouver le parrain
            $sponsor = User::where('sponsor_id', $request->sponsor_code)
                ->where('is_active', true)
                ->first();
                
            if (!$sponsor) {
                return redirect()->back()->with('error', 'Code parrain invalide ou inactif');
            }
            
            // 2. Creer ou recuperer le client
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
                ]);
                
                Wallet::create([
                    'user_id' => $client->id,
                    'balance' => 0,
                    'pending_balance' => 0,
                    'currency' => 'USD',
                    'is_active' => true,
                ]);
                
                $client->assignRole('user');
            } else {
                if (!$client->parrain_id) {
                    $client->parrain_id = $sponsor->id;
                    $client->save();
                }
            }
            
            // 3. Recuperer les produits
            $products = Product::whereIn('id', $productIds)->where('is_active', 1)->get();
            if ($products->isEmpty()) {
                return redirect()->back()->with('error', 'Aucun produit valide');
            }
            
            $subtotal = $products->sum('price');
            $total = $subtotal;
            $totalPv = $products->sum('pv_value');
            $totalBv = $products->sum('bv_value');
            $commissionAmount = $request->input('commission_amount', 0);
            
            DB::beginTransaction();
            
            try {
                // 4. Creer la commande
                $orderNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                
                $order = Order::create([
                    'user_id' => $client->id,
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
                        'product_count' => $products->count(),
                        'commission_amount' => $commissionAmount,
                    ],
                    'paid_at' => now(),
                ]);
                
                // 5. Creer les articles
                foreach ($products as $product) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'sku' => 'PROD-' . $product->id,
                        'quantity' => 1,
                        'price' => $product->price,
                        'total' => $product->price,
                        'pv_value' => $product->pv_value ?? 0,
                        'bv_value' => $product->bv_value ?? 0,
                    ]);
                    
                    // Réduire le stock
                    $product->decrement('stock', 1);
                }
                
                // 6. Crediter les PV au parrain
                if ($totalPv > 0) {
                    $sponsor->addPV($totalPv, 'pos_sale', $order->id);
                }
                
                // 7. CREER LA COMMISSION CASH (5$ à 15$)
                if ($commissionAmount > 0 && $commissionAmount >= 5 && $commissionAmount <= 15) {
                    Commission::create([
                        'user_id' => $sponsor->id,
                        'from_user_id' => $client->id,
                        'period' => now()->format('Y-m'),
                        'type' => 'direct',
                        'source' => 'pos',
                        'amount' => $commissionAmount,
                        'percentage' => 0,
                        'description' => "Commission CASH sur vente POS multi-produits - Commande #{$orderNumber}",
                        'notes' => "Montant: $" . number_format($commissionAmount, 2) . " (5$ à 15$) - " . $products->count() . " produits",
                        'order_id' => $order->id,
                        'status' => 'pending',
                    ]);
                }
                
                // 8. ACTIVITÉ POUR LE CAISSIER
                Commission::create([
                    'user_id' => auth()->id(),
                    'from_user_id' => $client->id,
                    'period' => now()->format('Y-m'),
                    'type' => 'pos_transaction',
                    'source' => 'pos',
                    'amount' => 0,
                    'percentage' => 0,
                    'description' => "Vente POS - Commande #{$orderNumber}",
                    'notes' => "Client: {$client->name} - " . $products->count() . " produit(s) - Total: $" . number_format($total, 2),
                    'order_id' => $order->id,
                    'status' => 'completed',
                ]);
                
                // 9. ACTIVITÉ POUR LE NOUVEAU CLIENT (si nouveau)
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
                
                // 10. ACTIVITÉ POUR L'ACHAT DU CLIENT
                Commission::create([
                    'user_id' => $client->id,
                    'from_user_id' => $sponsor->id,
                    'period' => now()->format('Y-m'),
                    'type' => 'purchase',
                    'source' => 'pos',
                    'amount' => $total,
                    'percentage' => 0,
                    'description' => "Achat POS - Commande #{$orderNumber}",
                    'notes' => $products->count() . " produit(s) - Total: $" . number_format($total, 2),
                    'order_id' => $order->id,
                    'status' => 'completed',
                ]);
                
                // 11. Mettre a jour le team PV
                UpdateTeamPV::dispatch($sponsor->id, true);
                
                // 12. Recalculer le grade
                $sponsor->calculateAndUpdateRank();
                
                DB::commit();
                
                return redirect()->route('cashier.orders.invoice', $order->id)
                    ->with('success', 'Vente #' . $orderNumber . ' validée avec ' . $products->count() . ' produits !');
                    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur creation commande multi-produits: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur validation: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
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
     * Historique des ventes POS
     */
    public function history(Request $request)
    {
        $query = Order::with(['user', 'items'])
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
        
        $stats = [
            'total_orders' => Order::where('source', 'pos')->where('status', 'completed')->count(),
            'total_sales' => Order::where('source', 'pos')->where('status', 'completed')->sum('total'),
            'total_pv' => Order::where('source', 'pos')->where('status', 'completed')->sum('total_pv'),
            'total_commissions' => Commission::where('source', 'pos')->where('status', 'paid')->sum('amount'),
        ];
        
        return view('cashier.history', compact('orders', 'stats'));
    }

    /**
     * Liste des commandes POS uniquement
     */
    public function orders()
    {
        $orders = Order::with(['user'])
            ->where('source', 'pos')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $stats = [
            'total' => Order::where('source', 'pos')->count(),
            'pending' => Order::where('source', 'pos')->where('status', 'pending')->count(),
            'completed' => Order::where('source', 'pos')->where('status', 'completed')->count(),
            'cancelled' => Order::where('source', 'pos')->where('status', 'cancelled')->count(),
        ];
        
        return view('cashier.orders', compact('orders', 'stats'));
    }

    public function showOrder($id)
    {
        $order = Order::with(['user', 'items'])
            ->where('source', 'pos')
            ->findOrFail($id);
        return view('cashier.order-detail', compact('order'));
    }

    public function invoice($id)
    {
        $order = Order::with(['user', 'items'])
            ->where('source', 'pos')
            ->findOrFail($id);
        
        $sponsor = null;
        if (isset($order->metadata['sponsor_id'])) {
            $sponsor = User::find($order->metadata['sponsor_id']);
        }
        
        return view('cashier.invoice', compact('order', 'sponsor'));
    }

    public function customers()
    {
        $customers = User::where('user_type', 'client')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('cashier.customers', compact('customers'));
    }

    public function profile()
    {
        $user = auth()->user();
        return view('cashier.profile', compact('user'));
    }

    public function dailySales()
    {
        $sales = Order::whereDate('created_at', today())
            ->where('source', 'pos')
            ->where('status', 'completed')
            ->get();

        $stats = [
            'total_orders' => $sales->count(),
            'total_amount' => $sales->sum('total'),
            'average_order' => $sales->avg('total'),
            'payment_methods' => $sales->groupBy('payment_method')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('total'),
                ];
            }),
        ];

        return view('cashier.daily-sales', compact('stats', 'sales'));
    }

    /**
     * Liste des commissions POS uniquement
     */
    public function commissions(Request $request)
    {
        $query = Commission::with(['user', 'fromUser', 'order'])
            ->where('source', 'pos');
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
        
        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total_commissions' => Commission::where('source', 'pos')->sum('amount'),
            'paid_commissions' => Commission::where('source', 'pos')->where('status', 'paid')->sum('amount'),
            'pending_commissions' => Commission::where('source', 'pos')->where('status', 'pending')->sum('amount'),
            'total_members' => Commission::where('source', 'pos')->distinct('user_id')->count('user_id'),
        ];
        
        $members = User::whereHas('commissions', function($q) {
            $q->where('source', 'pos');
        })->get(['id', 'name', 'sponsor_id']);
        
        return view('cashier.commission-fixed', compact('commissions', 'stats', 'members'));
    }

    public function searchCustomer(Request $request)
    {
        $search = $request->get('q');

        $customers = User::where('is_active', true)
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

    public function createCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|unique:users',
            'address' => 'nullable|string',
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
                'password' => Hash::make('password123'),
                'is_active' => true,
                'user_type' => 'client',
                'sponsor_id' => $this->generateSponsorId(),
                'rank_id' => 1,
                'rank' => 'Distributeur',
                'rank_level' => 1,
                'kyc_status' => 'not_submitted',
                'pv_balance' => 0,
                'bv_balance' => 0,
                'monthly_pv' => 0,
                'monthly_bv' => 0,
                'team_pv' => 0,
                'team_bv' => 0,
            ]);

            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);

            $user->assignRole('user');

            return response()->json([
                'success' => true,
                'message' => 'Client cree avec succes',
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

    private function generateSponsorId(): string
    {
        $prefix = 'SAL';
        do {
            $random = strtoupper(Str::random(6));
            $code = $prefix . $random;
        } while (User::where('sponsor_id', $code)->exists());
        
        return $code;
    }
}