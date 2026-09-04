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
use Illuminate\Support\Facades\Auth;
use App\Models\Genealogy;
use App\Models\Rank;
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
        'total_orders_today' => Order::whereDate('created_at', today())
            ->whereIn('source', ['pos', 'membership']) // AJOUTER 'membership'
            ->count(),
        'total_sales_today' => Order::whereDate('created_at', today())
            ->whereIn('source', ['pos', 'membership']) // AJOUTER 'membership'
            ->sum('total'),
        'customers_today' => User::whereDate('created_at', today())
            ->where('user_type', 'client')
            ->count(),
        'pending_orders' => Order::where('status', 'pending')
            ->whereIn('source', ['pos', 'membership']) // AJOUTER 'membership'
            ->count(),
    ];

    $recentOrders = Order::with(['user'])
        ->whereIn('source', ['pos', 'membership']) // AJOUTER 'membership'
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
     * Trouver un produit par son SKU (code-barres)
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
     * Exporter les commissions en PDF
     */
    public function exportPdf(Request $request)
    {
        $period = $request->input('period', date('Y-m'));
        
        $excludedTypes = ['pos_transaction', 'purchase', 'new_client'];
        
        $commissions = Commission::with(['user', 'fromUser'])
            ->where('period', $period)
            ->where('status', 'paid')
            ->whereNotIn('type', $excludedTypes)
            ->get();

        $members = [];
        $totals = [
            'sponsor' => 0,
            'direct' => 0,
            'indirect' => 0,
            'leadership' => 0,
            'cash_pos' => 0,
            'grand_total' => 0,
        ];

        foreach ($commissions as $commission) {
            $userId = $commission->user_id;
            if (!isset($members[$userId])) {
                $members[$userId] = [
                    'user' => $commission->user,
                    'period' => $period,
                    'sponsor' => 0,
                    'direct' => 0,
                    'indirect' => 0,
                    'leadership' => 0,
                    'cash_pos' => 0,
                    'monthly_pv' => $commission->user?->monthly_pv ?? 0,
                ];
            }

            $type = $commission->type;
            if (isset($members[$userId][$type])) {
                $members[$userId][$type] += $commission->amount;
                $totals[$type] += $commission->amount;
                $totals['grand_total'] += $commission->amount;
            }
        }

        foreach ($members as &$member) {
            $member['total'] = $member['sponsor'] + $member['direct'] + 
                               $member['indirect'] + $member['leadership'] + 
                               $member['cash_pos'];
        }

        $logoPath = public_path('images/logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoContent = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        } else {
            $logoBase64 = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 50"><text x="10" y="30" font-family="Arial" font-size="20" fill="#0E2F76">Salang Group</text></svg>');
        }

        $date = now();

        $html = view('cashier.commissions-pdf', compact(
            'members',
            'totals',
            'period',
            'logoBase64',
            'date'
        ))->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        
        $fileName = 'rapport_commissions_' . $period . '.pdf';
        
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Cache-Control', 'public, max-age=86400')
            ->header('Content-Length', strlen($pdf->output()))
            ->header('Pragma', 'public')
            ->header('Expires', '0');
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
     * Afficher la fiche de paie d'un membre
     */
    public function memberPaySlip($id)
    {
        $member = User::where('user_type', 'member')
            ->with(['package', 'parrain'])
            ->findOrFail($id);
        
        $period = request()->input('period', date('Y-m'));
        
        $commissions = Commission::where('user_id', $member->id)
            ->where('period', $period)
            ->where('status', 'paid')
            ->get();
        
        $totals = [
            'sponsor' => $commissions->where('type', 'sponsor')->sum('amount'),
            'direct' => $commissions->where('type', 'direct')->sum('amount'),
            'indirect' => $commissions->where('type', 'indirect')->sum('amount'),
            'leadership' => $commissions->where('type', 'leadership')->sum('amount'),
            'cash_pos' => $commissions->where('type', 'cash_pos')->sum('amount'),
        ];
        
        $totalCommissions = array_sum($totals);
        $monthlyPv = $member->monthly_pv ?? 0;
        $teamPv = $member->team_pv ?? 0;
        $directSponsors = User::where('parrain_id', $member->id)->where('user_type', 'member')->count();
        $posClients = User::where('parrain_id', $member->id)->where('user_type', 'client')->count();
        
        $commissionDetails = $commissions->groupBy('from_user_id')->map(function($items) {
            $user = $items->first()->fromUser;
            return [
                'user' => $user,
                'user_type' => $user?->user_type ?? 'inconnu',
                'sponsor' => $items->where('type', 'sponsor')->sum('amount'),
                'direct' => $items->where('type', 'direct')->sum('amount'),
                'indirect' => $items->where('type', 'indirect')->sum('amount'),
                'leadership' => $items->where('type', 'leadership')->sum('amount'),
                'cash_pos' => $items->where('type', 'cash_pos')->sum('amount'),
                'total' => $items->sum('amount'),
            ];
        });
        
        $periods = Commission::where('user_id', $member->id)
            ->where('status', 'paid')
            ->distinct()
            ->pluck('period')
            ->sort()
            ->reverse()
            ->values();
        
        return view('cashier.members.pay-slip', compact(
            'member',
            'period',
            'totals',
            'totalCommissions',
            'monthlyPv',
            'teamPv',
            'directSponsors',
            'posClients',
            'commissionDetails',
            'periods'
        ));
    }

    /**
     * Générer la fiche de paie en PDF
     */
    public function memberPaySlipPdf($id)
    {
        $member = User::where('user_type', 'member')
            ->with(['package', 'parrain'])
            ->findOrFail($id);
        
        $period = request()->input('period', date('Y-m'));
        
        $commissions = Commission::where('user_id', $member->id)
            ->where('period', $period)
            ->where('status', 'paid')
            ->get();
        
        $totals = [
            'sponsor' => $commissions->where('type', 'sponsor')->sum('amount'),
            'direct' => $commissions->where('type', 'direct')->sum('amount'),
            'indirect' => $commissions->where('type', 'indirect')->sum('amount'),
            'leadership' => $commissions->where('type', 'leadership')->sum('amount'),
            'cash_pos' => $commissions->where('type', 'cash_pos')->sum('amount'),
        ];
        
        $totalCommissions = array_sum($totals);
        $monthlyPv = $member->monthly_pv ?? 0;
        $teamPv = $member->team_pv ?? 0;
        $directSponsors = User::where('parrain_id', $member->id)->where('user_type', 'member')->count();
        $posClients = User::where('parrain_id', $member->id)->where('user_type', 'client')->count();
        
        $commissionDetails = $commissions->groupBy('from_user_id')->map(function($items) {
            $user = $items->first()->fromUser;
            return [
                'user' => $user,
                'user_type' => $user?->user_type ?? 'inconnu',
                'sponsor' => $items->where('type', 'sponsor')->sum('amount'),
                'direct' => $items->where('type', 'direct')->sum('amount'),
                'indirect' => $items->where('type', 'indirect')->sum('amount'),
                'leadership' => $items->where('type', 'leadership')->sum('amount'),
                'cash_pos' => $items->where('type', 'cash_pos')->sum('amount'),
                'total' => $items->sum('amount'),
            ];
        });
        
        $logoPath = public_path('images/salang_logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoContent = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        }
        
        $date = now();
        
        $pdf = Pdf::loadView('cashier.members.pay-slip-pdf', compact(
            'member',
            'period',
            'totals',
            'totalCommissions',
            'monthlyPv',
            'teamPv',
            'directSponsors',
            'posClients',
            'commissionDetails',
            'logoBase64',
            'date'
        ));
        
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('fiche_paie_' . $member->sponsor_id . '_' . $period . '.pdf');
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
            
            $client = null;
            
            if ($request->filled('customer_id')) {
                $client = User::find($request->customer_id);
                Log::info('Client trouvé par ID:', ['id' => $client->id ?? null]);
            }
            
            if (!$client && $request->filled('phone')) {
                $client = User::where('phone', $request->phone)->first();
                Log::info('Client trouvé par téléphone:', ['phone' => $request->phone]);
            }
            
            if (!$client && $request->filled('customer_phone')) {
                $client = User::where('phone', $request->customer_phone)->first();
                Log::info('Client trouvé par customer_phone:', ['phone' => $request->customer_phone]);
            }
            
            if (!$client) {
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
        $order = Order::with(['user', 'cashier', 'items', 'items.product'])
            ->where(function($query) use ($id) {
                $query->where('id', $id)
                      ->orWhere('order_number', $id);
            })
            ->firstOrFail();
        
        $sponsor = null;
        if (isset($order->metadata['sponsor_id'])) {
            $sponsor = User::find($order->metadata['sponsor_id']);
        }
        
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

    // ============================================================
    // GESTION DES MEMBRES - AVEC CommissionDistributor
    // ============================================================

    /**
     * Afficher le formulaire de création d'un nouveau membre
     */
    public function memberCreate()
    {
        $packages = Package::where('is_active', true)->orderBy('price')->get();
        return view('cashier.members.create', compact('packages'));
    }

    /**
     * Enregistrer un nouveau membre (par le caissier)
     * UTILISE UNIQUEMENT CommissionDistributor
     */
    public function memberStore(Request $request)
    {
        Log::info('=== MEMBER STORE START ===');
        Log::info('Données reçues:', $request->all());

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'nullable|email|max:255|unique:users,email',
            'sponsor_code' => 'required|string|exists:users,sponsor_id',
            'member_code' => 'required|string|max:20|unique:users,sponsor_id',
            'package_id' => 'required|exists:packages,id',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'profession' => 'nullable|string|max:255',
            'identity_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:255',
            'mobile_money' => 'nullable|string|max:50',
            'signature_name' => 'nullable|string|max:255',
            'signature_date' => 'nullable|date',
            'signature_location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::error('Validation échouée:', $validator->errors()->toArray());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            Log::info('Transaction DB démarrée');

            // 1. Trouver le parrain
            $sponsor = User::where('sponsor_id', $request->sponsor_code)
                ->where('is_active', true)
                ->first();

            if (!$sponsor) {
                Log::error('Parrain non trouvé', ['sponsor_code' => $request->sponsor_code]);
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Code parrain invalide ou inactif')
                    ->withInput();
            }

            // 2. Récupérer le package
            $package = Package::find($request->package_id);
            if (!$package) {
                Log::error('Package non trouvé', ['package_id' => $request->package_id]);
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Package invalide')
                    ->withInput();
            }

            Log::info('Package sélectionné', [
                'id' => $package->id,
                'name' => $package->name,
                'pv_value' => $package->pv_value ?? 0,
                'price' => $package->price ?? 0,
            ]);

            // 3. Générer un email si non fourni
            $email = $request->email;
            if (!$email) {
                $nameParts = explode(' ', trim($request->name));
                $firstName = strtolower($nameParts[0] ?? 'user');
                $lastName = strtolower($nameParts[1] ?? '');
                $firstName = preg_replace('/[^a-z0-9]/', '', $firstName);
                $lastName = preg_replace('/[^a-z0-9]/', '', $lastName);
                $email = $firstName . ($lastName ? '.' . $lastName : '') . '@salanggroup.com';
                
                $counter = 1;
                $originalEmail = $email;
                while (User::where('email', $email)->exists()) {
                    $email = str_replace('@salanggroup.com', $counter . '@salanggroup.com', $originalEmail);
                    $counter++;
                }
            }

            $password = 'password123';
            $pvValue = $package->pv_value ?? 0;
            $bvValue = $package->bv_value ?? ($pvValue * 0.8);

            // 4. Déterminer le grade
            $rank = Rank::where('min_pv', '<=', $pvValue)
                ->where('is_active', true)
                ->orderBy('min_pv', 'desc')
                ->first();

            if ($rank) {
                $rankId = $rank->id;
                $rankName = $rank->name;
                $rankLevel = $rank->level;
            } else {
                $rankId = 1;
                $rankName = 'Distributeur';
                $rankLevel = 1;
            }

            // 5. CRÉER LE MEMBRE
            Log::info('Tentative de création du membre...');
            
            $member = User::create([
                'name' => $request->name,
                'email' => $email,
                'phone' => $request->phone,
                'password' => Hash::make($password),
                'sponsor_id' => $request->member_code,
                'parrain_id' => $sponsor->id,
                'is_active' => true,
                'user_type' => 'member',
                'kyc_status' => 'pending',
                'package_id' => $package->id,
                'rank' => $rankName,
                'rank_id' => $rankId,
                'rank_level' => $rankLevel,
                'pv_balance' => $pvValue,
                'bv_balance' => $bvValue,
                'monthly_pv' => $pvValue,
                'monthly_bv' => $bvValue,
                'team_pv' => 0,
                'team_bv' => 0,
                'total_team' => 0,
                'total_sponsors' => 0,
                'address' => $request->address,
                'city' => $request->city ?? 'Goma',
                'country' => $request->country ?? 'RDC',
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'profession' => $request->profession,
                'identity_number' => $request->identity_number,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder' => $request->account_holder,
                'mobile_money' => $request->mobile_money,
                'signature_name' => $request->signature_name,
                'signature_date' => $request->signature_date,
                'signature_location' => $request->signature_location,
                'registered_by' => auth()->id(),
                'registered_at' => now(),
                'metadata' => [
                    'package_name' => $package->name,
                    'package_pv' => $pvValue,
                    'rank_name' => $rankName,
                    'rank_level' => $rankLevel,
                    'sponsor_name' => $sponsor->name,
                    'sponsor_code' => $sponsor->sponsor_id,
                    'cashier_id' => auth()->id(),
                    'cashier_name' => auth()->user()->name,
                ],
            ]);
            
            Log::info('Membre créé avec succès!', [
                'id' => $member->id,
                'name' => $member->name,
                'sponsor_id' => $member->sponsor_id,
                'rank' => $member->rank,
            ]);

            // 6. Assigner le rôle
            try {
                $member->assignRole('member');
            } catch (\Exception $e) {
                Log::error('Erreur assignation rôle: ' . $e->getMessage());
            }

            // 7. Créer le wallet
            try {
                Wallet::create([
                    'user_id' => $member->id,
                    'balance' => 0,
                    'pending_balance' => 0,
                    'currency' => 'USD',
                    'is_active' => true,
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur création wallet: ' . $e->getMessage());
            }

            // 8. Créer l'entrée généalogique
            try {
                $level = ($sponsor->genealogy?->level ?? 0) + 1;
                Genealogy::create([
                    'user_id' => $member->id,
                    'sponsor_id' => $sponsor->id,
                    'parent_id' => $sponsor->id,
                    'level' => $level,
                    'position' => $this->getPosition($sponsor),
                    'left_count' => 0,
                    'right_count' => 0,
                    'total_children' => 0,
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur création généalogie: ' . $e->getMessage());
            }

            // 9. Mettre à jour les compteurs du sponsor
            $sponsor->increment('total_sponsors');
            $sponsor->increment('total_team');
            $sponsor->save();

            // 10. Mettre à jour le team_pv
            if (method_exists($member, 'updateTeamPVOptimized')) {
                try {
                    $member->updateTeamPVOptimized();
                } catch (\Exception $e) {
                    Log::error('Erreur updateTeamPVOptimized: ' . $e->getMessage());
                }
            }
            
            if (method_exists($member, 'updateAllAncestorsTeamPV')) {
                try {
                    $member->updateAllAncestorsTeamPV();
                } catch (\Exception $e) {
                    Log::error('Erreur updateAllAncestorsTeamPV: ' . $e->getMessage());
                }
            }

            // 11. Mettre à jour le grade du sponsor
            if (method_exists($sponsor, 'updateRankAfterPVChange')) {
                try {
                    $sponsor->updateRankAfterPVChange('new_member_with_package');
                } catch (\Exception $e) {
                    Log::error('Erreur updateRankAfterPVChange: ' . $e->getMessage());
                }
            }

            // ============================================================
            // 12. COMMISSIONS VIA CommissionDistributor UNIQUEMENT
            // ============================================================
            try {
                Log::info('🔄 Appel de CommissionDistributor pour adhésion...');

                // Créer une période de commission
                $period = CommissionPeriod::firstOrCreate(
                    ['period' => date('Y-m')],
                    [
                        'start_date' => now()->startOfMonth(),
                        'end_date' => now()->endOfMonth(),
                        'status' => 'pending',
                    ]
                );

                // CRÉER UNE COMMANDE POUR L'ADHÉSION
                $order = Order::create([
                    'user_id' => $member->id,
                    'order_number' => 'MEM-' . strtoupper(uniqid()),
                    'subtotal' => $package->price ?? 0,
                    'tax' => 0,
                    'shipping' => 0,
                    'discount' => 0,
                    'total' => $package->price ?? 0,
                    'total_pv' => $pvValue,
                    'total_bv' => $bvValue,
                    'status' => 'completed',
                    'payment_status' => 'completed',
                    'paid_at' => now(),
                    'source' => 'membership', // SOURCE IMPORTANTE !
                    'metadata' => [
                        'cashier_id' => auth()->id(),
                        'cashier_name' => auth()->user()->name,
                        'sponsor_id' => $sponsor->id,
                        'is_membership' => true,
                        'cashier_registration' => true,
                        'member_code' => $request->member_code,
                    ],
                ]);

                // CRÉER L'ITEM DE LA COMMANDE
                OrderItem::create([
                    'order_id' => $order->id,
                    'package_id' => $package->id,
                    'name' => $package->name,
                    'sku' => 'PKG-' . $package->slug,
                    'quantity' => 1,
                    'price' => $package->price ?? 0,
                    'total' => $package->price ?? 0,
                    'pv_value' => $pvValue,
                    'bv_value' => $bvValue,
                    'options' => json_encode([
                        'package_id' => $package->id,
                        'package_name' => $package->name,
                        'membership_type' => 'cashier_registration',
                    ]),
                ]);

                // ============================================================
                // APPEL À CommissionDistributor
                // ============================================================
                $commissionDistributor = app(\App\Services\MLM\CommissionDistributor::class);
                $commissions = $commissionDistributor->distributeCommissions(
                    $member,      // L'acheteur (le nouveau membre)
                    $package,      // Le package acheté
                    $order->id,    // L'ID de la commande
                    $period        // La période de commission
                );

                $totalAmount = collect($commissions)->sum('amount');

                Log::info('✅ Commissions calculées par CommissionDistributor', [
                    'user_id' => $member->id,
                    'member_name' => $member->name,
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    'commissions_count' => count($commissions),
                    'total_amount' => $totalAmount,
                    'commission_types' => collect($commissions)->pluck('type')->unique()->toArray(),
                ]);

                // Mettre à jour les jobs de classement
                if (class_exists('App\Jobs\UpdateRanks')) {
                    try {
                        \App\Jobs\UpdateRanks::dispatch($member->id)->onQueue('high');
                        \App\Jobs\UpdateRanks::dispatch($sponsor->id)->onQueue('high');
                        Log::info('Jobs de mise à jour des rangs dispatchés');
                    } catch (\Exception $e) {
                        Log::error('Erreur dispatch UpdateRanks: ' . $e->getMessage());
                    }
                }

            } catch (\Exception $e) {
                Log::error('❌ Erreur CommissionDistributor: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            DB::commit();
            Log::info('✅ Transaction DB validée avec succès!');

            return redirect()->route('cashier.members.show', $member->id)
                ->with('success', 'Membre enregistré avec succès !')
                ->with('password', $password)
                ->with('email', $email)
                ->with('member_code', $request->member_code)
                ->with('package_name', $package->name)
                ->with('rank_name', $rankName)
                ->with('pv_value', $pvValue);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERREUR CRITIQUE: ' . $e->getMessage());
            Log::error('Trace:', $e->getTrace());
            
            return redirect()->back()
                ->with('error', 'Erreur: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Déterminer la position dans l'arbre généalogique
     */
    private function getPosition($sponsor): string
    {
        $leftCount = User::where('parrain_id', $sponsor->id)
            ->where('position', 'left')
            ->count();
            
        $rightCount = User::where('parrain_id', $sponsor->id)
            ->where('position', 'right')
            ->count();

        if ($leftCount <= $rightCount) {
            return 'left';
        }
        return 'right';
    }

 /**
 * Historique des ventes POS et ADHÉSIONS
 */
public function history(Request $request)
{
    $query = Order::with(['user', 'cashier', 'items'])
        ->whereIn('source', ['pos', 'membership'])
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
    
    // Statistiques incluant les adhésions
    $allOrdersQuery = Order::with(['items'])
        ->whereIn('source', ['pos', 'membership']) // AJOUTER 'membership'
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
        'total_orders' => Order::whereIn('source', ['pos', 'membership'])->where('status', 'completed')->count(),
        'total_sales' => Order::whereIn('source', ['pos', 'membership'])->where('status', 'completed')->sum('total'),
        'total_pv' => $totalPvAll,
        'total_commissions' => Commission::where('source', 'pos')
            ->where('status', 'paid')
            ->where('type', 'cash_pos')
            ->sum('amount'),
    ];
    
    return view('cashier.history', compact('orders', 'stats'));
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
     * Vérifier si un code membre est disponible (AJAX)
     */
    public function checkMemberCode(Request $request)
    {
        $code = $request->get('code', '');
        
        if (empty($code)) {
            return response()->json([
                'available' => false,
                'message' => 'Code requis'
            ]);
        }
        
        $exists = User::where('sponsor_id', $code)->exists();
        
        if ($exists) {
            return response()->json([
                'available' => false,
                'message' => 'Ce code est déjà attribué à un autre membre'
            ]);
        }
        
        return response()->json([
            'available' => true,
            'message' => '✓ Code disponible'
        ]);
    }

    /**
     * Rechercher des parrains existants (AJAX)
     */
    public function searchSponsors(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $sponsors = User::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%")
                  ->orWhere('sponsor_id', 'LIKE', "%{$query}%");
            })
            ->whereIn('user_type', ['member', 'admin'])
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone', 'sponsor_id']);
        
        return response()->json($sponsors);
    }

    /**
     * Liste des membres
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
            'with_commissions' => Commission::where('status', 'paid')->distinct('user_id')->count('user_id'),
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
        
        $commissionsQuery = Commission::where('user_id', $member->id)
            ->orderBy('created_at', 'desc');
        
        if (request()->filled('type')) {
            $commissionsQuery->where('type', request()->type);
        }
        if (request()->filled('status')) {
            $commissionsQuery->where('status', request()->status);
        }
        if (request()->filled('source')) {
            $commissionsQuery->where('source', request()->source);
        }
        
        $commissions = $commissionsQuery->paginate(15);
        
        $downlines = User::where('parrain_id', $member->id)
            ->where('user_type', 'member')
            ->with(['package'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $stats = [
            'total_commissions' => Commission::where('user_id', $member->id)->where('status', 'paid')->sum('amount'),
            'paid_commissions' => Commission::where('user_id', $member->id)->where('status', 'paid')->sum('amount'),
            'pending_commissions' => Commission::where('user_id', $member->id)->where('status', 'pending')->sum('amount'),
            'approved_commissions' => Commission::where('user_id', $member->id)->where('status', 'approved')->count(),
            'cancelled_commissions' => Commission::where('user_id', $member->id)->where('status', 'cancelled')->count(),
            'total_downlines' => $downlines->total(),
            'active_downlines' => User::where('parrain_id', $member->id)->where('is_active', true)->count(),
        ];
        
        return view('cashier.members.show', compact('member', 'commissions', 'stats', 'downlines'));
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
     * Commissions d'un membre
     */
    public function memberCommissions($id)
    {
        $member = User::where('user_type', 'member')->findOrFail($id);
        
        $commissions = Commission::where('user_id', $member->id)
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $stats = [
            'total' => Commission::where('user_id', $member->id)->where('status', 'paid')->sum('amount'),
            'paid' => Commission::where('user_id', $member->id)->where('status', 'paid')->sum('amount'),
            'pending' => Commission::where('user_id', $member->id)->where('status', 'pending')->sum('amount'),
            'approved' => Commission::where('user_id', $member->id)->where('status', 'approved')->count(),
        ];
        
        return view('cashier.members.commissions', compact('member', 'commissions', 'stats'));
    }

    // ============================================================
    // COMMISSIONS
    // ============================================================

    /**
     * Liste toutes les commissions
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
        $sources = ['pos' => 'POS', 'mlm' => 'MLM', 'membership' => 'Adhésion'];
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

        $member = $commission->user;
        
        $commissions = Commission::where('user_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $downlines = User::where('parrain_id', $member->id)
            ->where('user_type', 'member')
            ->with(['package'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $orders = Order::where('user_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

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
            'member',
            'commissions',
            'downlines',
            'orders',
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
 * Générer le formulaire d'adhésion en PDF
 */
public function generateAdhesionForm($id)
{
    try {
        $member = User::where('user_type', 'member')->with(['package', 'parrain'])->findOrFail($id);
        
        $sponsor = null;
        if ($member->parrain_id) {
            $sponsor = User::find($member->parrain_id);
        }
        
        // Récupérer les métadonnées
        $metadata = $member->metadata ?? [];
        
        // Logo en base64 avec gestion d'erreur
        $logoBase64 = '';
        $logoPath = public_path('images/salang_logo.png');
        if (file_exists($logoPath)) {
            try {
                $logoContent = file_get_contents($logoPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
            } catch (\Exception $e) {
                Log::error('Erreur chargement logo: ' . $e->getMessage());
                $logoBase64 = '';
            }
        }
        
        $data = [
            'member' => $member,
            'sponsor' => $sponsor,
            'metadata' => $metadata,
            'logoBase64' => $logoBase64,
            'date' => now(),
        ];
        
        $pdf = Pdf::loadView('cashier.adhesion-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('formulaire_adhesion_' . $member->sponsor_id . '.pdf');
        
    } catch (\Exception $e) {
        Log::error('Erreur génération PDF: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
    }
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

    // ============================================================
    // PROFIL
    // ============================================================

    public function profile()
    {
        $user = auth()->user();
        return view('cashier.profile', compact('user'));
    }

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

    public function deleteAvatar(Request $request)
    {
        $user = auth()->user();
        
        if ($user->avatar && file_exists(storage_path('app/public/avatars/' . $user->avatar))) {
            unlink(storage_path('app/public/avatars/' . $user->avatar));
        }
        
        $user->update(['avatar' => null]);
        
        return response()->json(['success' => true, 'message' => 'Avatar supprimé.']);
    }

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
}