<?php

namespace App\Http\Controllers\Cashier;


use App\Support\MlmPeriod;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Consultation;
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
use App\Http\Controllers\Admin\AdminCashierController;
use App\Http\Controllers\Concerns\DispatchesMlmRecalculation;
use App\Http\Controllers\Concerns\GeneratesPdfSafely;
use App\Notifications\CashierMemberRegisteredNotification;
use App\Notifications\CommissionPaidNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\ActivityEntry;
use App\Services\ActivityRegisterService;
use App\Services\ConsultationSaleService;
use App\Services\PvManagementService;

class CashierController extends Controller
{
    use DispatchesMlmRecalculation;
    use GeneratesPdfSafely;

    protected $pvService;

    public function __construct(PvManagementService $pvService)
    {
        // INITIALISER LE SERVICE
        $this->pvService = $pvService;
        
        $this->middleware(['auth', 'active']);
        
        // it_manager a son propre portail (ItManager\*) — volontairement hors caisse POS.
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

    $recentOrders = Order::with(['user.parrain'])
        ->whereIn('source', ['pos', 'membership']) // AJOUTER 'membership'
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    $sponsorIds = $recentOrders
        ->map(fn ($order) => data_get($order->metadata, 'sponsor_id'))
        ->filter()
        ->unique()
        ->values();
    $sponsorsById = $sponsorIds->isEmpty()
        ? collect()
        : User::query()->whereIn('id', $sponsorIds)->get()->keyBy('id');

    return view('cashier.dashboard', compact('stats', 'recentOrders', 'sponsorsById'));
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

        $commissionAmount = $this->validatedCashPosAmount($request);
        
        $sponsor = $this->resolvePosSponsor($request->sponsor_code);
            
        if (!$sponsor) {
            return redirect()->back()->with('error', 'Code parrain invalide, inactif ou non membre');
        }
        
        if ($request->filled('phone')) {
            $memberWithPhone = User::where('user_type', 'member')->where('phone', $request->phone)->first(['id', 'name', 'sponsor_id']);
            if ($memberWithPhone) {
                return redirect()->back()->with(
                    'error',
                    'Ce numéro appartient au membre '
                    .($memberWithPhone->sponsor_id ? $memberWithPhone->sponsor_id.' : ' : '')
                    .$memberWithPhone->name
                    .'. Utilisez un autre téléphone client.'
                );
            }
        }

        $client = User::where('user_type', 'client')->where('phone', $request->phone)->first();
        
        if (!$client) {
            $client = User::create([
                'name' => $request->name,
                'email' => $request->email ?: $this->makeClientPlaceholderEmail((string) $request->phone),
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
            $this->syncClientParrainIfMissing($client, $sponsor);
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
        
        DB::beginTransaction();
        
        try {
            $this->decrementProductStock((int) $product->id, (int) $quantity, $product->name);

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
            
            // ============================================================
            // GESTION DES PV - VENTE POS
            // Les PV vont UNIQUEMENT dans user_pv_balances du PARRAIN
            // Ils seront synchronisés vers users UNIQUEMENT après distribution + approbation
            // ============================================================
            if ($totalPv > 0 && $sponsor) {
                
                // 1. NOUVEAU SYSTÈME : user_pv_balances (PARRAIN)
                // Les PV sont donnés au PARRAIN pour qu'il les distribue
                // Ils ne vont PAS encore dans team_pv !
                $this->pvService->addPvFromSale($sponsor, $order, [
                    'pv' => $totalPv,
                    'bv' => $totalBv
                ]);
                
                Log::info('PV ajoutés au PARRAIN (disponibles pour distribution)', [
                    'parrain_id' => $sponsor->id,
                    'parrain_name' => $sponsor->name,
                    'pv_added' => $totalPv,
                    'bv_added' => $totalBv,
                    'client_id' => $client->id,
                    'order_id' => $order->id,
                ]);
            }
            
            // Commission CASH POS
            if ($commissionAmount >= 4) {
                $this->createPaidCashPosCommission(
                    $sponsor,
                    $client,
                    $order,
                    $orderNumber,
                    $commissionAmount,
                    "Montant: $" . number_format($commissionAmount, 2) . " (4$ à 15$) - Payé en espèce"
                );
                
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
            
            // Mise à jour du grade du sponsor (sans team_pv car pas encore distribué)
            // Le grade du sponsor sera recalculé après les distributions
            // UpdateTeamPV::dispatch($sponsor->id, true);
            // $sponsor->calculateAndUpdateRank();
            
            $this->saveOrderData($order, $commissionAmount);

            session()->forget('pos_cart');
            session()->forget('consultation_checkout');
            
            DB::commit();
            
            return redirect()->route('cashier.orders.invoice', $order->id)
                ->with('success', 'Vente #' . $orderNumber . ' validée avec succès ! PV disponibles pour distribution.')
                ->with('clear_cart', true);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création commande POS: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
        
    } catch (ValidationException $e) {
        throw $e;
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
        $period = $request->input('period', MlmPeriod::current());
        
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

        $logoBase64 = $this->loadPdfLogoBase64();
        $date = now();

        $html = view('cashier.commissions-pdf', compact(
            'members',
            'totals',
            'period',
            'logoBase64',
            'date'
        ))->render();

        $this->preparePdfExport(300, '512M');
        $pdf = Pdf::loadHTML($html)
            ->setPaper('A4', 'landscape')
            ->setOptions($this->safePdfOptions());

        return $this->downloadPdfBinary(
            $pdf->output(),
            'rapport_commissions_'.$period.'.pdf'
        );
    }

/**
 * Legacy multi-order : délègue au checkout unifié (évite double logique / stock).
 */
public function createMultiOrder(Request $request)
{
    Log::info('=== CREATION COMMANDE POS (MULTI-PRODUITS) → checkout unifié ===');

    return $this->createCheckoutOrder($request);
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
        
        $sponsor = $this->resolvePosSponsor($code);
            
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
            'message' => 'Code parrain invalide ou non membre'
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
                $cart[$key] = $this->enrichPosCartItem([
                    'id' => $product->id,
                    'type' => 'product',
                    'name' => $product->name,
                    'image' => $product->image,
                    'source' => 'pos',
                    'source_label' => 'POS',
                    'bv_value' => $product->bv_value ?? 0,
                    'quantity' => $quantity,
                    'portion' => ConsultationSaleService::PORTION_FULL,
                ]);
            } else {
                // Vente package = adhésion membre (pas de panier POS)
                $package = Package::where('is_active', true)->find($productId);
                if (! $package) {
                    return response()->json(['success' => false, 'message' => 'Package non trouvé'], 404);
                }

                return response()->json([
                    'success' => true,
                    'redirect' => route('cashier.members.create', ['package_id' => $package->id]),
                    'message' => 'Redirection vers le formulaire d\'adhésion',
                ]);
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
     * Applique Complet / 1/2 sur une ligne du panier POS (checkout).
     */
    public function updateCartPortion(Request $request)
    {
        $data = $request->validate([
            'index' => ['required', 'integer', 'min:0'],
            'portion' => ['required', 'string', 'in:full,half'],
        ]);

        $cart = array_values(session()->get('pos_cart', []));
        $index = (int) $data['index'];

        if (! isset($cart[$index])) {
            return response()->json(['success' => false, 'message' => 'Ligne introuvable.'], 404);
        }

        $item = $cart[$index];
        if (($item['type'] ?? 'product') !== 'product') {
            return response()->json(['success' => false, 'message' => 'Portion disponible uniquement pour les produits.'], 422);
        }

        $product = Product::query()->find($item['id'] ?? null);
        if ($data['portion'] === ConsultationSaleService::PORTION_HALF
            && (! $product || ! $product->hasHalfOption())) {
            return response()->json(['success' => false, 'message' => 'Ce produit ne se vend pas en 1/2.'], 422);
        }

        $item['portion'] = ConsultationSaleService::resolvePortion($data['portion']);
        $cart[$index] = $this->enrichPosCartItem($item);
        session()->put('pos_cart', $cart);

        $consultationCheckout = session('consultation_checkout');
        if (is_array($consultationCheckout) && ! empty($cart[$index]['consultation_line_key'])) {
            $lineKey = $cart[$index]['consultation_line_key'];
            $details = $consultationCheckout['purchase_details'] ?? [];
            $details[$lineKey] = [
                'portion' => $cart[$index]['portion'] ?? ConsultationSaleService::PORTION_FULL,
                'prix' => $cart[$index]['price'] ?? 0,
            ];
            $consultationCheckout['purchase_details'] = $details;
            session()->put('consultation_checkout', $consultationCheckout);
        }

        $total = collect($cart)->sum(fn ($row) => ((float) ($row['price'] ?? 0)) * ((int) ($row['quantity'] ?? 1)));
        $totalPv = collect($cart)->sum(fn ($row) => ((int) ($row['pv_value'] ?? 0)) * ((int) ($row['quantity'] ?? 1)));
        $totalBv = collect($cart)->sum(fn ($row) => ((int) ($row['bv_value'] ?? 0)) * ((int) ($row['quantity'] ?? 1)));

        return response()->json([
            'success' => true,
            'item' => $cart[$index],
            'totals' => [
                'total' => round($total, 2),
                'total_pv' => $totalPv,
                'total_bv' => $totalBv,
                'count' => count($cart),
            ],
        ]);
    }

    /**
     * Complète prix / PV / option Complet·1/2 d’une ligne panier depuis le catalogue.
     * L’option 1/2 n’apparaît que si le produit a prix 1/2 (+ PV 1/2) renseignés.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function enrichPosCartItem(array $item): array
    {
        if (($item['type'] ?? 'product') !== 'product') {
            return $item;
        }

        $product = Product::query()->find($item['id'] ?? null);
        if (! $product) {
            $item['portion'] = ConsultationSaleService::PORTION_FULL;
            $item['has_half'] = false;

            return $item;
        }

        $hasHalf = $product->hasHalfOption();
        $portion = ConsultationSaleService::resolvePortionForProduct($product, $item['portion'] ?? null);
        $baseName = preg_replace('/\s*\(1\/2\)\s*$/u', '', (string) ($item['name'] ?? $product->name)) ?: $product->name;

        $item['portion'] = $portion;
        $item['has_half'] = $hasHalf;
        $item['full_price'] = $product->priceForPortion(ConsultationSaleService::PORTION_FULL);
        $item['half_price'] = $hasHalf
            ? $product->priceForPortion(ConsultationSaleService::PORTION_HALF)
            : null;
        $item['full_pv'] = $product->pvForPortion(ConsultationSaleService::PORTION_FULL);
        $item['half_pv'] = $hasHalf
            ? $product->pvForPortion(ConsultationSaleService::PORTION_HALF)
            : null;
        $item['price'] = ConsultationSaleService::priceForProduct($product, $portion);
        $item['pv_value'] = ConsultationSaleService::pvForProduct($product, $portion);
        $item['name'] = ConsultationSaleService::productDisplayName([
            'produit' => $baseName,
            'portion' => $portion,
        ]);

        return $item;
    }

    /**
     * Vider le panier POS (session) et renvoyer au Point de Vente.
     */
    public function clearCart()
    {
        session()->forget('pos_cart');
        session()->forget('consultation_checkout');

        return redirect()
            ->route('cashier.pos')
            ->with('success', 'Panier vidé.');
    }

    /**
     * Afficher la fiche de paie d'un membre
     */
    public function memberPaySlip($id)
    {
        $member = $this->cashierMembersQuery()
            ->with(['package', 'parrain'])
            ->findOrFail($id);
        
        $period = request()->input('period', MlmPeriod::current());
        
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
        $member = $this->cashierMembersQuery()
            ->with(['package', 'parrain'])
            ->findOrFail($id);
        
        $period = request()->input('period', MlmPeriod::current());
        
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
        
        $logoBase64 = $this->loadPdfLogoBase64();
        $date = now();

        return $this->downloadPdfView(
            'cashier.members.pay-slip-pdf',
            compact(
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
            ),
            'fiche_paie_'.($member->sponsor_id ?: $member->id).'_'.$period.'.pdf',
            'a4',
            'portrait',
            180,
            '512M'
        );
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
                        $cart[] = $this->enrichPosCartItem([
                            'id' => $product->id,
                            'type' => 'product',
                            'name' => $product->name,
                            'image' => $product->image ? asset('storage/products/' . $product->image) : null,
                            'source' => 'pos',
                            'source_label' => 'POS',
                            'bv_value' => $product->bv_value ?? 0,
                            'quantity' => 1,
                            'portion' => ConsultationSaleService::PORTION_FULL,
                        ]);
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
                            'source' => 'pos',
                            'source_label' => 'Package',
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

        // Package = adhésion : rediriger vers le formulaire membre
        $packageItem = $cartItems->first(fn ($item) => ($item['type'] ?? null) === 'package');
        if ($packageItem) {
            return redirect()
                ->route('cashier.members.create', ['package_id' => $packageItem['id']])
                ->with('info', 'La vente d\'un package se fait via le formulaire d\'adhésion membre.');
        }

        $cartItems = $cartItems
            ->values()
            ->map(fn ($item) => $this->enrichPosCartItem(is_array($item) ? $item : (array) $item));

        $total = $cartItems->sum(function ($item) {
            return ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        });

        $totalPv = $cartItems->sum(function ($item) {
            return ((int) ($item['pv_value'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        });

        $totalBv = $cartItems->sum(function ($item) {
            return ((int) ($item['bv_value'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        });

        if ($cartItems->isNotEmpty()) {
            session()->put('pos_cart', $cartItems->values()->toArray());
        }
        
        Log::info('Affichage de la vue checkout avec ' . $cartItems->count() . ' articles');

        $consultationCheckout = session('consultation_checkout');
        $linkedConsultation = null;
        if (! empty($consultationCheckout['consultation_id'])) {
            $linkedConsultation = Consultation::with('client')->find($consultationCheckout['consultation_id']);
            if ($linkedConsultation && empty($consultationCheckout['pv_member_code'])) {
                $consultationCheckout['pv_member_code'] = $linkedConsultation->code_id;
                $consultationCheckout['pv_member_name'] = $linkedConsultation->client?->name
                    ?? User::where('sponsor_id', $linkedConsultation->code_id)->value('name');
                session()->put('consultation_checkout', $consultationCheckout);
            }
        }

        return view('cashier.checkout', compact(
            'cartItems',
            'total',
            'totalPv',
            'totalBv',
            'consultationCheckout',
            'linkedConsultation'
        ));
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

        $commissionAmount = $this->validatedCashPosAmount($request);

        $cart = array_values(session()->get('pos_cart', []));
        $cart = array_map(fn ($item) => $this->enrichPosCartItem(is_array($item) ? $item : (array) $item), $cart);
        if (! empty($cart)) {
            session()->put('pos_cart', $cart);
        }

        $cartFingerprint = ! empty($cart) ? $this->posCartFingerprint($cart) : null;

        $consultationCheckoutForGuard = session('consultation_checkout');
        $consultationIdForGuard = ! empty($consultationCheckoutForGuard['consultation_id'])
            ? (int) $consultationCheckoutForGuard['consultation_id']
            : null;

        // BB1-rapide — avant le refus panier vide (le 1er POST vide déjà le panier)
        $recentOrderQuery = Order::query()
            ->where('cashier_id', auth()->id())
            ->where('created_at', '>=', now()->subMinutes(2))
            ->where('status', '!=', 'cancelled');

        $recentOrder = null;

        if ($consultationIdForGuard) {
            $candidates = (clone $recentOrderQuery)
                ->where('consultation_id', $consultationIdForGuard)
                ->get();
            $recentOrder = $cartFingerprint
                ? $candidates->first(function (Order $order) use ($cartFingerprint) {
                    $fp = data_get($order->metadata, 'cart_fingerprint');

                    return $fp === null || $fp === $cartFingerprint;
                })
                : $candidates->first();
        } else {
            $guardUserId = $request->input('customer_id');
            if (! $guardUserId && $request->filled('phone')) {
                $guardUserId = User::where('user_type', 'client')->where('phone', $request->phone)->value('id');
            }
            if (! $guardUserId && $request->filled('customer_phone')) {
                $guardUserId = User::where('user_type', 'client')->where('phone', $request->customer_phone)->value('id');
            }
            if ($guardUserId) {
                $candidates = (clone $recentOrderQuery)
                    ->where('user_id', $guardUserId)
                    ->get();
                // Panier vide = double-clic après succès → reprendre la commande récente.
                // Panier rempli = n'intercepter que le même panier (autre vente légitime OK).
                $recentOrder = $cartFingerprint
                    ? $candidates->first(function (Order $order) use ($cartFingerprint) {
                        $fp = data_get($order->metadata, 'cart_fingerprint');

                        return $fp === null || $fp === $cartFingerprint;
                    })
                    : $candidates->first();
            }
        }

        if ($recentOrder) {
            Log::warning('BB1-rapide : checkout récent détecté (double-clic évité)', [
                'existing_order_id' => $recentOrder->id,
                'customer_id' => $request->input('customer_id'),
                'consultation_id' => $consultationIdForGuard,
                'cart_fingerprint' => $cartFingerprint,
            ]);

            return redirect()
                ->route('cashier.orders.show', $recentOrder->id)
                ->with('info', 'Une commande récente existe déjà. Redirection vers la commande existante.');
        }

        if (empty($cart)) {
            return redirect()->back()->with('error', 'Votre panier est vide.');
        }

        // Package = adhésion membre : ne pas encaisser via le checkout POS
        $packageInCart = collect($cart)->first(fn ($item) => ($item['type'] ?? null) === 'package');
        if ($packageInCart) {
            return redirect()
                ->route('cashier.members.create', ['package_id' => $packageInCart['id']])
                ->with('info', 'La vente d\'un package se fait via le formulaire d\'adhésion membre.');
        }

        $consultationCheckout = session('consultation_checkout');
        if (is_array($consultationCheckout)) {
            $details = $consultationCheckout['purchase_details'] ?? [];
            foreach ($cart as $item) {
                if (empty($item['consultation_line_key'])) {
                    continue;
                }
                $details[$item['consultation_line_key']] = [
                    'portion' => $item['portion'] ?? ConsultationSaleService::PORTION_FULL,
                    'prix' => $item['price'] ?? 0,
                ];
            }
            $consultationCheckout['purchase_details'] = $details;
            session()->put('consultation_checkout', $consultationCheckout);
        }
        $consultationId = null;
        $consultationForSale = null;
        if (! empty($consultationCheckout['consultation_id'])) {
            $consultationForSale = Consultation::find($consultationCheckout['consultation_id']);
            if (! $consultationForSale || $consultationForSale->cashier_id !== auth()->id()) {
                return redirect()->back()->with('error', 'Consultation liée invalide.');
            }
            if (! ConsultationSaleService::canCashierSell($consultationForSale)) {
                return redirect()->back()->with('error', 'Cette consultation n\'accepte plus d\'encaissement.');
            }

            $expectedKeys = $consultationCheckout['line_keys'] ?? [];
            $cartKeys = collect($cart)
                ->pluck('consultation_line_key')
                ->filter()
                ->values()
                ->all();

            sort($expectedKeys);
            sort($cartKeys);

            if ($expectedKeys !== $cartKeys) {
                return redirect()->back()->with('error', 'Le panier ne correspond pas à la sélection de la consultation.');
            }

            try {
                ConsultationSaleService::resolvePendingLines($consultationForSale, $expectedKeys);
            } catch (\InvalidArgumentException $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }

            $consultationId = $consultationForSale->id;
        }

        if ($consultationForSale && $consultationForSale->code_id
            && $request->sponsor_code !== $consultationForSale->code_id) {
            return redirect()->back()->with(
                'error',
                'Pour une vente consultation, le Code ID doit être celui de la fiche (membre destinataire des PV).'
            );
        }

        $sponsor = $this->resolvePosSponsor($request->sponsor_code);

        if (! $sponsor) {
            return redirect()->back()->with('error', 'Code membre / parrain invalide, inactif ou non membre');
        }

        // Client POS = uniquement user_type=client.
        // Si le téléphone appartient déjà à un membre → vente refusée.
        $client = null;

        if ($request->filled('customer_id')) {
            $client = User::query()
                ->where('id', $request->customer_id)
                ->where('user_type', 'client')
                ->first();
            Log::info('Client trouvé par ID:', ['id' => $client->id ?? null, 'requested' => $request->customer_id]);
        }

        $lookupPhone = $request->input('phone') ?: $request->input('customer_phone');
        if (filled($lookupPhone)) {
            $memberWithPhone = User::query()
                ->where('user_type', 'member')
                ->where('phone', $lookupPhone)
                ->first(['id', 'name', 'sponsor_id', 'phone']);

            if ($memberWithPhone) {
                return redirect()->back()->with(
                    'error',
                    'Ce numéro appartient au membre '
                    .($memberWithPhone->sponsor_id ? $memberWithPhone->sponsor_id.' : ' : '')
                    .$memberWithPhone->name
                    .'. Utilisez un autre téléphone client, ou vendez via le compte membre approprié.'
                );
            }
        }

        if (! $client && filled($lookupPhone)) {
            $client = User::query()
                ->where('user_type', 'client')
                ->where('phone', $lookupPhone)
                ->first();
            Log::info('Client trouvé par téléphone:', [
                'phone' => $lookupPhone,
                'id' => $client->id ?? null,
            ]);
        }

        if (! $client) {
            $clientName = $request->input('name') ?: $request->input('customer_name');
            $clientPhone = $request->input('phone') ?: $request->input('customer_phone');

            if ($consultationForSale && ! filled($clientName)) {
                $clientName = $consultationForSale->nom_complet;
            }

            if (! filled($clientName) || ! filled($clientPhone)) {
                return redirect()->back()->with('error', 'Veuillez remplir le nom et le téléphone du client.');
            }

            $client = User::create([
                'name' => $clientName,
                'email' => $request->email
                    ?: ($request->customer_email ?: $this->makeClientPlaceholderEmail((string) $clientPhone)),
                'phone' => $clientPhone,
                'password' => bcrypt(Str::random(12)),
                'sponsor_id' => 'CLT'.strtoupper(Str::random(6)),
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
                'address' => $request->address ?: $request->customer_address,
                'city' => $request->city ?: $request->customer_city,
                'country' => $request->country ?: $request->customer_country,
            ]);

            Wallet::create([
                'user_id' => $client->id,
                'balance' => 0,
                'pending_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);

            Log::info('Nouveau client créé:', ['id' => $client->id, 'parrain_id' => $sponsor->id]);
        } else {
            $this->syncClientParrainIfMissing($client, $sponsor);
            if ($request->filled('address') || $request->filled('customer_address')) {
                $client->address = $request->input('address') ?: $request->input('customer_address');
            }
            if ($request->filled('city') || $request->filled('customer_city')) {
                $client->city = $request->input('city') ?: $request->input('customer_city');
            }
            if ($request->filled('country') || $request->filled('customer_country')) {
                $client->country = $request->input('country') ?: $request->input('customer_country');
            }
            $client->save();
            Log::info('Client mis à jour:', ['id' => $client->id, 'parrain_id' => $sponsor->id]);
        }
        
        $subtotal = 0;
        $totalPv = 0;
        $totalBv = 0;
        
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $totalPv += ($item['pv_value'] ?? 0) * $item['quantity'];
            $totalBv += ($item['bv_value'] ?? 0) * $item['quantity'];
        }
        
        $total = $subtotal;
        
        DB::beginTransaction();
        
        try {
            $orderNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            
            // Vente POS (produit ou package) ≠ adhésion : l'adhésion passe par l'inscription membre.
            $order = Order::create([
                'user_id' => $client->id,
                'cashier_id' => auth()->id(),
                'consultation_id' => $consultationId,
                'created_by' => auth()->id(),
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'tax' => 0,
                'shipping' => 0,
                'discount' => 0,
                'total' => $total,
                'total_pv' => $totalPv,
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
                    'consultation_id' => $consultationId,
                    'cart_fingerprint' => $cartFingerprint,
                    'has_package' => collect($cart)->contains(fn ($i) => ($i['type'] ?? '') === 'package'),
                ],
                'paid_at' => now(),
            ]);

            if ($consultationId && $consultationForSale && ! empty($consultationCheckout['line_keys'])) {
                ConsultationSaleService::markLinesPurchased(
                    $consultationForSale,
                    $consultationCheckout['line_keys'],
                    $order->id,
                    $consultationCheckout['purchase_details'] ?? []
                );
            }
            
            foreach ($cart as $item) {
                $type = $item['type'] ?? null;
                $product = null;
                $package = null;

                if ($type === 'package') {
                    $package = Package::find($item['id']);
                } elseif ($type === 'product') {
                    $product = Product::find($item['id']);
                } else {
                    // Panier legacy sans type explicite
                    $product = Product::find($item['id']);
                    if (! $product) {
                        $package = Package::find($item['id']);
                    }
                }

                if (! $product && ! $package) {
                    throw new \RuntimeException('Article introuvable dans le panier (id='.$item['id'].').');
                }
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product?->id,
                    'package_id' => $package?->id,
                    'name' => $item['name'],
                    'sku' => $product ? 'PROD-'.$product->id : 'PKG-'.$package->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                    'pv_value' => $item['pv_value'] ?? 0,
                    'bv_value' => $item['bv_value'] ?? 0,
                ]);
                
                if ($product) {
                    $this->decrementProductStock(
                        (int) $product->id,
                        (int) $item['quantity'],
                        $item['name'] ?? $product->name
                    );
                }
            }
            
            // ============================================================
            // GESTION DES PV - CHECKOUT
            // ============================================================
            if ($totalPv > 0 && $sponsor) {
                
                // 1. NOUVEAU SYSTÈME : user_pv_balances (PARRAIN)
                $this->pvService->addPvFromSale($sponsor, $order, [
                    'pv' => $totalPv,
                    'bv' => $totalBv
                ]);
                
                Log::info('PV ajoutés au PARRAIN (checkout)', [
                    'parrain_id' => $sponsor->id,
                    'parrain_name' => $sponsor->name,
                    'pv_added' => $totalPv,
                    'bv_added' => $totalBv,
                    'client_id' => $client->id,
                ]);
            }
            
            if ($commissionAmount >= 4) {
                $commissionNotes = "Montant: $" . number_format($commissionAmount, 2) . " (4$ à 15$) - "
                    . count($cart) . " produits - Payé en espèce";
                if ($consultationId) {
                    $commissionNotes .= " - Consultation #{$consultationId}"
                        . " - Bénéficiaire: {$sponsor->name} ({$sponsor->sponsor_id})";
                }

                $this->createPaidCashPosCommission(
                    $sponsor,
                    $client,
                    $order,
                    $orderNumber,
                    $commissionAmount,
                    $commissionNotes
                );
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
                'notes' => "Client: {$client->name} - " . count($cart) . " produit(s) - Total: $" . number_format($total, 2)
                    . ($consultationId ? " - Consultation #{$consultationId}" : ''),
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
            
            // Mise à jour du grade du sponsor (sans team_pv car pas encore distribué)
            // UpdateTeamPV::dispatch($sponsor->id, true);
            // $sponsor->calculateAndUpdateRank();
            
            $this->saveOrderData($order, $commissionAmount);
            
            session()->forget('pos_cart');
            session()->forget('consultation_checkout');
            
            DB::commit();

            $successMessage = 'Commande #' . $orderNumber . ' validée avec ' . count($cart) . ' produits ! PV disponibles pour distribution.';
            if ($consultationId) {
                $successMessage .= ' Liée à la consultation #' . $consultationId . '.';
            }
            
            return redirect()->route('cashier.orders.invoice', $order->id)
                ->with('success', $successMessage)
                ->with('clear_cart', true);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création commande checkout: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
        }
        
    } catch (ValidationException $e) {
        throw $e;
    } catch (\Exception $e) {
        Log::error('Erreur validation: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
    }
}

/**
 * Supprimer définitivement le compte du caissier
 */
public function destroyProfile(Request $request)
{
    $request->validateWithBag('userDeletion', [
        'password' => ['required', 'string', 'current_password'],
    ]);
    
    $user = $request->user();
    
    // Vérifier que l'utilisateur est bien un caissier ou admin
    if (!$user->hasRole('cashier') && !$user->hasRole('admin')) {
        abort(403);
    }
    
    // Sécurité : Empêcher un admin de se supprimer lui-même ?
    // (à adapter selon votre logique métier)
    // if ($user->hasRole('admin')) {
    //     return back()->with('error', 'Un admin ne peut pas supprimer son compte.');
    // }
    
    try {
        DB::beginTransaction();
        
        // Déconnecter l'utilisateur
        auth()->logout();
        
        // Supprimer les données liées (ordre important)
        // Décommentez selon votre schéma
        
        // $user->wishlist()->delete();
        // $user->cart()->delete();
        // $user->notifications()->delete();
        // if ($user->wallet) $user->wallet->delete();
        // Commission::where('user_id', $user->id)->delete();
        // Transaction::where('user_id', $user->id)->delete();
        
        // Supprimer l'avatar si existe
        if ($user->avatar && file_exists(storage_path('app/public/avatars/' . $user->avatar))) {
            unlink(storage_path('app/public/avatars/' . $user->avatar));
        }
        
        // Supprimer l'utilisateur
        $user->delete();
        
        DB::commit();
        
        // Invalider la session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home')
            ->with('success', 'Votre compte a été supprimé avec succès.');
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur suppression compte caissier: ' . $e->getMessage());
        
        return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
    }
}

    /**
     * Liste des commandes (POS + En ligne + MLM)
     */
    public function orders(Request $request)
    {
        $query = Order::with(['user.parrain', 'cashier']);
        
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

        $sponsorIds = $orders->getCollection()
            ->map(fn ($order) => data_get($order->metadata, 'sponsor_id'))
            ->filter()
            ->unique()
            ->values();
        $sponsorsById = $sponsorIds->isEmpty()
            ? collect()
            : User::query()->whereIn('id', $sponsorIds)->get()->keyBy('id');
        
        $stats = [
            'total' => Order::count(),
            'pos_count' => Order::where('source', 'pos')->count(),
            'web_count' => Order::whereIn('source', ['web', 'online'])->count(),
            'mlm_count' => Order::where('source', 'mlm')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];
        
        return view('cashier.orders', compact('orders', 'stats', 'sponsorsById'));
    }

    /**
     * Détails d'une commande
     */
    public function showOrder(Request $request, $id)
    {
        $order = Order::with([
            'user.parrain',
            'cashier',
            'items.product',
            'items.package',
            'commissions' => function ($q) {
                $q->whereIn('type', ['sponsor', 'cash_pos']);
            },
        ])
            ->where(function($query) use ($id) {
                $query->where('id', $id)
                      ->orWhere('order_number', $id);
            })
            ->firstOrFail();

        $fromHistory = $request->query('from') === 'history';
        $sponsorCommission = (float) $order->commissions->where('type', 'sponsor')->sum('amount');
        $cashCommission = (float) $order->commissions->where('type', 'cash_pos')->sum('amount');
        $totalCommission = $sponsorCommission + $cashCommission;

        return view('cashier.order-detail', compact(
            'order',
            'fromHistory',
            'sponsorCommission',
            'cashCommission',
            'totalCommission'
        ));
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
        
        return $this->downloadPdfView(
            'cashier.invoice-pdf',
            compact('order', 'sponsor'),
            'facture_'.$order->order_number.'.pdf',
            'a4',
            'portrait',
            120,
            '256M'
        );
    }

    /**
     * Annuler une commande (pending/processing uniquement — les completed passent par demande admin).
     */
    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);
        
        if ($order->status === 'cancelled') {
            return redirect()->back()->with('error', 'Cette commande est déjà annulée.');
        }
        
        if ($order->status === 'completed') {
            return redirect()->back()->with(
                'error',
                'Une commande complétée ne peut pas être annulée directement. Utilisez « Demander l\'annulation » (délai 10 min).'
            );
        }

        if (! in_array($order->status, ['pending', 'processing'], true)) {
            return redirect()->back()->with('error', 'Cette commande ne peut pas être annulée.');
        }

        DB::beginTransaction();
        try {
            $this->applyPosOrderCancellationEffects($order);

            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur cancelOrder: '.$e->getMessage(), ['order_id' => $order->id]);

            return redirect()->back()->with('error', 'Erreur lors de l\'annulation: '.$e->getMessage());
        }
        
        return redirect()->back()->with('success', 'Commande annulée avec succès.');
    }

    /**
     * Demande d'annulation (POS completed) — validation admin requise.
     */
    public function requestCancellation(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $reason = trim((string) $request->input('reason', ''));
        if (strlen($reason) < 10) {
            return response()->json([
                'success' => false,
                'message' => 'Le motif doit contenir au moins 10 caractères.',
            ], 422);
        }

        if ($order->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande est déjà annulée.',
            ], 422);
        }

        if (! in_array($order->status, ['pending', 'processing', 'completed'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut pas faire l\'objet d\'une demande d\'annulation.',
            ], 422);
        }

        if ($order->created_at->diffInMinutes(now()) > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Le délai de 10 minutes pour demander l\'annulation est dépassé.',
            ], 422);
        }

        $metadata = $order->metadata ?? [];
        $existing = $metadata['cancellation_request'] ?? null;
        if (is_array($existing) && ($existing['status'] ?? null) === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Une demande d\'annulation est déjà en attente.',
            ], 422);
        }

        $metadata['cancellation_request'] = [
            'status' => 'pending',
            'reason' => $reason,
            'requested_at' => now()->toISOString(),
            'requested_by' => auth()->id(),
            'requested_by_name' => auth()->user()->name,
        ];

        $order->metadata = $metadata;
        $order->save();

        Log::info('Demande d\'annulation POS créée', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'cashier_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande d\'annulation envoyée à l\'administrateur.',
        ]);
    }

    /**
     * Payer en masse toutes les commissions pending/approved d'un membre.
     */
    public function forcePayAllCommissions($id)
    {
        $member = $this->cashierMembersQuery()->findOrFail($id);

        $commissions = Commission::query()
            ->where('user_id', $member->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('amount', '>', 0)
            ->get();

        if ($commissions->isEmpty()) {
            return redirect()->back()->with('info', 'Aucune commission à payer pour ce membre.');
        }

        $paid = 0;

        try {
            DB::beginTransaction();

            foreach ($commissions as $commission) {
                $commission->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                Wallet::creditCommissionToUser(
                    (int) $member->id,
                    (float) $commission->amount,
                    'Paiement forcé: '.($commission->description ?? 'Commission #'.$commission->id),
                    ['commission_id' => $commission->id, 'forced_by' => auth()->id()]
                );

                $paid++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur forcePayAllCommissions: '.$e->getMessage(), ['member_id' => $member->id]);

            return redirect()->back()->with('error', 'Erreur: '.$e->getMessage());
        }

        return redirect()->back()->with(
            'success',
            $paid.' commission(s) payée(s) pour '.$member->name.'.'
        );
    }

    /**
     * Liste des clients
     */
    public function customers(Request $request)
    {
        $query = User::where('user_type', 'client')->with(['parrain:id,name,sponsor_id']);
        
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
        $customer = User::with([
            'parrain:id,name,sponsor_id,phone',
            'orders' => fn ($q) => $q->orderByDesc('created_at')->limit(30),
            'orders.items',
        ])
            ->where('user_type', 'client')
            ->findOrFail($id);

        $orders = $customer->orders;
        
        $stats = [
            'total_orders' => $orders->count(),
            'total_spent' => $orders->where('status', '!=', 'cancelled')->sum('total'),
            'completed_orders' => $orders->where('status', 'completed')->count(),
        ];
        
        return view('cashier.customers.show', compact('customer', 'stats', 'orders'));
    }

    /**
     * Créer un client (AJAX)
     */
    public function createCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|max:20',
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

        $phone = trim((string) $request->phone);
        if ($phone === '' || strcasecmp($phone, 'N/A') === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Le téléphone client est obligatoire.',
            ], 422);
        }

        $memberWithPhone = User::query()
            ->where('user_type', 'member')
            ->where('phone', $phone)
            ->first(['id', 'name', 'sponsor_id']);

        if ($memberWithPhone) {
            return response()->json([
                'success' => false,
                'message' => 'Ce numéro appartient au membre '
                    .($memberWithPhone->sponsor_id ? $memberWithPhone->sponsor_id.' : ' : '')
                    .$memberWithPhone->name
                    .'. Utilisez un autre téléphone client.',
            ], 422);
        }

        if (User::query()->where('user_type', 'client')->where('phone', $phone)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Un client existe déjà avec ce téléphone.',
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $phone,
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
     * (aussi accessible depuis le POS : vente package = adhésion)
     */
    public function memberCreate(Request $request)
    {
        $packages = Package::where('is_active', true)->orderBy('price')->get();
        $selectedPackageId = $request->integer('package_id') ?: (int) old('package_id');

        if ($selectedPackageId && ! $packages->contains('id', $selectedPackageId)) {
            $selectedPackageId = 0;
        }

        $activityEntry = null;
        $entryId = $request->integer('activity_entry') ?: (int) old('activity_entry_id');
        if ($entryId) {
            $activityEntry = ActivityEntry::query()
                ->with(['member:id,name,sponsor_id,phone,email', 'sheet:id,activity_date'])
                ->where('adhesion_done', false)
                ->find($entryId);
        }

        return view('cashier.members.create', compact('packages', 'selectedPackageId', 'activityEntry'));
    }

    /**
     * Recherche d’invités du registre IT en attente d’adhésion.
     */
    public function searchActivityGuests(Request $request, ActivityRegisterService $register)
    {
        $guests = $register->searchPendingGuests((string) $request->query('q', ''));

        return response()->json(
            $guests->map(function (ActivityEntry $entry) {
                return [
                    'id' => $entry->id,
                    'name' => $entry->displayName(),
                    'nom' => $entry->participant_nom,
                    'postnom' => $entry->participant_postnom,
                    'prenom' => $entry->participant_prenom,
                    'phone' => $entry->participant_phone,
                    'email' => $entry->participant_email,
                    'birth_date' => $entry->participant_birth_date?->toDateString(),
                    'gender' => $entry->participant_gender,
                    'address' => $entry->participant_address,
                    'profession' => $entry->participant_profession,
                    'identity_number' => $entry->participant_identity_number,
                    'member_code' => $entry->member_code ?? $entry->member?->sponsor_id,
                    'member_name' => $entry->member?->name,
                    'activity_date' => $entry->sheet?->activity_date?->format('d/m/Y'),
                    'url' => route('cashier.members.create', ['activity_entry' => $entry->id]),
                ];
            })->values()
        );
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
            'member_code' => ['required', 'string', 'regex:/^\d{6}$/', 'unique:users,sponsor_id'],
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
            'activity_entry_id' => 'nullable|integer|exists:activity_entries,id',
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
                $localPart = '';
                foreach ($nameParts as $part) {
                    $localPart .= preg_replace('/[^a-z0-9]/', '', strtolower($part));
                }
                if ($localPart === '') {
                    $localPart = 'user';
                }
                $email = $localPart.'@salanggroup.com';

                $counter = 1;
                while (User::where('email', $email)->exists()) {
                    $email = $localPart.$counter.'@salanggroup.com';
                    $counter++;
                }
            }

            $password = Str::password(12);
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

            // Lier explicitement la fiche invité du registre IT (si fournie)
            if ($request->filled('activity_entry_id')) {
                try {
                    $forcedEntry = ActivityEntry::query()
                        ->where('adhesion_done', false)
                        ->find($request->integer('activity_entry_id'));

                    if ($forcedEntry) {
                        app(ActivityRegisterService::class)->markAdhesion($member, now(), $forcedEntry);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Lien registre activité échoué', [
                        'member_id' => $member->id,
                        'activity_entry_id' => $request->integer('activity_entry_id'),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // 6. Assigner le rôle
            try {
                $member->assignRole('member');
            } catch (\Exception $e) {
                Log::error('Erreur assignation rôle: ' . $e->getMessage());
            }

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
                Log::error('Erreur création généalogie: '.$e->getMessage());
            }

            $sponsor->increment('total_sponsors');
            $sponsor->increment('total_team');
            $sponsor->save();

            // ============================================================
            // Commande POS + commission parrain (CommissionDistributor)
            // ============================================================
            try {
                Log::info('🔄 Appel de CommissionDistributor pour adhésion...');

                // Créer une période de commission
                $period = CommissionPeriod::firstOrCreate(
                    ['period' => MlmPeriod::current()],
                    [
                        'start_date' => now()->startOfMonth(),
                        'end_date' => now()->endOfMonth(),
                        'status' => 'pending',
                    ]
                );

                $orderNumber = 'POS-'.date('Ymd').'-'.strtoupper(Str::random(6));

                $order = Order::create([
                    'user_id' => $member->id,
                    'cashier_id' => auth()->id(),
                    'created_by' => auth()->id(),
                    'order_number' => $orderNumber,
                    'subtotal' => $package->price ?? 0,
                    'tax' => 0,
                    'shipping' => 0,
                    'discount' => 0,
                    'total' => $package->price ?? 0,
                    'total_pv' => $pvValue,
                    'status' => 'completed',
                    'payment_status' => 'completed',
                    'payment_method' => 'cash',
                    'paid_at' => now(),
                    'source' => 'membership',
                    'metadata' => [
                        'cashier_id' => auth()->id(),
                        'cashier_name' => auth()->user()->name,
                        'sponsor_id' => $sponsor->id,
                        'is_membership' => true,
                        'cashier_registration' => true,
                        'member_code' => $request->member_code,
                        'pos_sale' => false,
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
                // PV déjà crédités sur le profil membre à la création — sponsor bonus uniquement
                $commissions = $commissionDistributor->distributeCommissions(
                    $member,
                    $package,
                    $order->id,
                    $period,
                    true
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

            } catch (\Exception $e) {
                Log::error('❌ Erreur CommissionDistributor: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            DB::commit();
            Log::info('✅ Transaction DB validée avec succès!');

            $emailSent = false;
            // Mail d’accueil + identifiants uniquement si un e-mail a été renseigné (ex. Gmail)
            if ($request->filled('email') && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    $member->notify(new CashierMemberRegisteredNotification(
                        $password,
                        (string) $request->member_code,
                        $sponsor->name,
                        $package->name,
                    ));
                    $emailSent = true;
                } catch (\Throwable $e) {
                    Log::error('Envoi mail adhésion caissier échoué', [
                        'member_id' => $member->id,
                        'email' => $email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $successMessage = $emailSent
                ? 'Membre enregistré avec succès ! Un e-mail avec les identifiants a été envoyé à '.$email.'.'
                : 'Membre enregistré avec succès !';

            return redirect()->route('cashier.members.show', $member->id)
                ->with('success', $successMessage)
                ->with('password', $password)
                ->with('email', $email)
                ->with('member_code', $request->member_code)
                ->with('package_name', $package->name)
                ->with('rank_name', $rankName)
                ->with('pv_value', $pvValue)
                ->with('email_sent', $emailSent);

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
     * Crée une commission cash_pos payée et crédite le portefeuille du parrain.
     */
    /**
     * Parrain POS = membre actif hors staff (pas client CLT…, pas caissier/admin).
     */
    protected function resolvePosSponsor(?string $code): ?User
    {
        if (! filled($code)) {
            return null;
        }

        $staffRoles = AdminCashierController::STAFF_ROLES;

        return User::query()
            ->where('sponsor_id', $code)
            ->where('is_active', true)
            ->where('user_type', 'member')
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', $staffRoles))
            ->first();
    }

    /**
     * Commission CASH POS : 0 (aucune) ou entre 4$ et 15$.
     */
    protected function validatedCashPosAmount(Request $request): float
    {
        $amount = (float) ($request->input('commission_amount') ?? 0);

        if ($amount <= 0) {
            return 0.0;
        }

        if ($amount < 4 || $amount > 15) {
            throw ValidationException::withMessages([
                'commission_amount' => 'La commission CASH POS doit être entre 4$ et 15$ (ou 0 si aucune).',
            ]);
        }

        return $amount;
    }

    /**
     * Empreinte panier pour la garde anti double-clic (même panier ≠ autre vente).
     */
    protected function posCartFingerprint(array $cart): string
    {
        $normalized = collect($cart)
            ->map(fn ($item) => [
                'id' => $item['id'] ?? null,
                'type' => $item['type'] ?? 'product',
                'qty' => (int) ($item['quantity'] ?? 1),
                'price' => round((float) ($item['price'] ?? 0), 2),
                'portion' => $item['portion'] ?? null,
            ])
            ->sortBy(fn ($item) => ($item['type'] ?? '').'-'.($item['id'] ?? ''))
            ->values()
            ->all();

        return hash('sha256', json_encode($normalized));
    }

    /**
     * Décrémente le stock de façon atomique ; refuse si insuffisant.
     */
    protected function decrementProductStock(int $productId, int $quantity, ?string $productName = null): void
    {
        if ($quantity < 1) {
            return;
        }

        $affected = Product::query()
            ->where('id', $productId)
            ->where('stock', '>=', $quantity)
            ->decrement('stock', $quantity);

        if ($affected === 0) {
            $product = Product::query()->find($productId);
            $available = (int) ($product?->stock ?? 0);
            $label = $productName ?: ($product?->name ?? ('#'.$productId));

            throw new \RuntimeException(
                "Stock insuffisant pour {$label} (disponible: {$available}, demandé: {$quantity})."
            );
        }
    }

    protected function restoreOrderStock(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if (! $item->product_id || (int) $item->quantity < 1) {
                continue;
            }

            Product::query()
                ->where('id', $item->product_id)
                ->increment('stock', (int) $item->quantity);
        }
    }

    /**
     * Annule les commissions cash_pos payées et reverse le crédit wallet si possible.
     */
    protected function reverseOrderCashPosCommissions(Order $order): void
    {
        $commissions = Commission::query()
            ->where('order_id', $order->id)
            ->where('type', 'cash_pos')
            ->where('status', 'paid')
            ->get();

        foreach ($commissions as $commission) {
            $wallet = Wallet::query()->where('user_id', $commission->user_id)->lockForUpdate()->first();
            if ($wallet && (float) $wallet->balance >= (float) $commission->amount) {
                try {
                    $wallet->debit(
                        (float) $commission->amount,
                        'Annulation commission CASH POS #'.$order->order_number
                    );
                } catch (\Throwable $e) {
                    Log::warning('Impossible de débiter le wallet pour annulation cash_pos', [
                        'commission_id' => $commission->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $commission->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'notes' => trim(($commission->notes ?? '').' | Annulée avec la commande'),
            ]);
        }

        Commission::query()
            ->where('order_id', $order->id)
            ->whereIn('type', ['pos_transaction', 'purchase', 'new_client'])
            ->where('status', '!=', 'cancelled')
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ]);
    }

    /**
     * Effets métier d'une annulation POS approuvée (stock, PV, commissions).
     */
    public function applyPosOrderCancellationEffects(Order $order): void
    {
        $this->restoreOrderStock($order);
        $this->reverseOrderCashPosCommissions($order);
        $this->pvService->reversePvFromSale($order);
    }

    /**
     * Ne fixe le parrain client que s'il n'en a pas encore (la vente courante
     * utilise toujours le sponsor saisi via metadata / PV / cash_pos).
     */
    protected function syncClientParrainIfMissing(User $client, User $sponsor): void
    {
        if (! $client->parrain_id) {
            $client->parrain_id = $sponsor->id;
        }
    }

    /**
     * Email technique unique pour un client POS sans adresse fournie.
     */
    protected function makeClientPlaceholderEmail(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: 'client';
        $email = $digits.'@client.pos.local';

        if (! User::query()->where('email', $email)->exists()) {
            return $email;
        }

        do {
            $email = $digits.'+'.Str::lower(Str::random(6)).'@client.pos.local';
        } while (User::query()->where('email', $email)->exists());

        return $email;
    }

    private function createPaidCashPosCommission(
        User $sponsor,
        User $client,
        Order $order,
        string $orderNumber,
        float $commissionAmount,
        string $notes
    ): Commission {
        $commission = Commission::create([
            'user_id' => $sponsor->id,
            'from_user_id' => $client->id,
            'period' => MlmPeriod::current(),
            'type' => 'cash_pos',
            'source' => 'pos',
            'amount' => $commissionAmount,
            'percentage' => 0,
            'description' => "Commission CASH POS - Commande #{$orderNumber}",
            'notes' => $notes,
            'order_id' => $order->id,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // CASH POS = payée en espèces au guichet ; le crédit wallet est un miroir optionnel.
        $wallet = Wallet::creditCommissionToUser(
            (int) $sponsor->id,
            (float) $commissionAmount,
            $commission->description,
            ['commission_id' => $commission->id, 'type' => 'cash_pos']
        );

        if ($wallet === null) {
            Log::info('Commission cash_pos enregistrée sans crédit wallet (bénéficiaire non éligible)', [
                'sponsor_id' => $sponsor->id,
                'commission_id' => $commission->id,
                'amount' => $commissionAmount,
            ]);
        }

        return $commission;
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
    $query = Order::with([
            'user.parrain:id,name,sponsor_id,user_type,team_pv',
            'cashier:id,name',
            'items',
            'commissions' => fn ($q) => $q->whereIn('type', ['sponsor', 'cash_pos']),
        ])
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
    
    $orders = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

    $sponsorIds = $orders->getCollection()
        ->map(fn ($order) => data_get($order->metadata, 'sponsor_id'))
        ->filter()
        ->unique()
        ->values();
    $sponsorsById = $sponsorIds->isEmpty()
        ? collect()
        : User::query()
            ->whereIn('id', $sponsorIds)
            ->get(['id', 'name', 'sponsor_id', 'user_type', 'team_pv'])
            ->keyBy('id');
    
    // Statistiques incluant les adhésions
    $allOrdersQuery = Order::with(['items'])
        ->whereIn('source', ['pos', 'membership'])
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
    
    $commissionQuery = Commission::query()
        ->where('source', 'pos')
        ->where('status', 'paid')
        ->where('type', 'cash_pos')
        ->whereHas('order', function ($q) use ($request) {
            $q->whereIn('source', ['pos', 'membership'])->where('status', 'completed');
            if ($request->filled('date_from')) {
                $q->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $q->whereDate('created_at', '<=', $request->date_to);
            }
        });

    $stats = [
        'total_orders' => $allOrders->count(),
        'total_sales' => $allOrders->sum('total'),
        'total_pv' => $totalPvAll,
        'total_commissions' => $commissionQuery->sum('amount'),
    ];
    
    return view('cashier.history', compact('orders', 'stats', 'sponsorsById'));
}

    /**
     * Rapport PDF de l'historique des ventes POS (filtres de la page historique).
     */
    public function historyExportPdf(Request $request)
    {
        try {
            $this->preparePdfExport(300, '512M');

            $query = Order::with([
                'user:id,name,phone',
                'items.product:id,name',
                'items.package:id,name',
                'commissions' => function ($q) {
                    $q->whereIn('type', ['sponsor', 'cash_pos']);
                },
            ])
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
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($sub) use ($search) {
                            $sub->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('phone', 'LIKE', "%{$search}%");
                        });
                });
            }

            $orders = $query->orderBy('created_at', 'desc')->limit(3000)->get();

            $sponsorIds = $orders
                ->map(fn ($order) => $order->metadata['sponsor_id'] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $sponsors = $sponsorIds
                ? User::whereIn('id', $sponsorIds)->get(['id', 'name', 'sponsor_id'])->keyBy('id')
                : collect();

            $rows = $orders->map(function (Order $order) use ($sponsors) {
                $firstItem = $order->items->first();
                $isMembership = ($order->source ?? '') === 'membership'
                    || isset($order->metadata['is_membership']);

                $itemKind = 'other';
                $itemLabel = $order->items->count().' article(s)';
                $itemExtra = null;

                if ($isMembership) {
                    $itemKind = 'membership';
                    $pkgName = $firstItem?->package?->name;
                    $itemLabel = $pkgName ?: 'Adhésion';
                    if (! empty($order->metadata['member_code'])) {
                        $itemExtra = 'Code: '.$order->metadata['member_code'];
                    }
                } elseif ($firstItem?->package_id) {
                    $itemKind = 'package';
                    $itemLabel = $firstItem->package?->name ?? ('Package #'.$firstItem->package_id);
                    $itemExtra = 'Qté: '.$order->items->sum('quantity');
                } elseif ($firstItem?->product_id) {
                    $itemKind = 'product';
                    $itemLabel = $firstItem->product?->name ?? ('Produit #'.$firstItem->product_id);
                    $itemExtra = 'Qté: '.$order->items->sum('quantity');
                }

                $sponsor = null;
                if (! empty($order->metadata['sponsor_id'])) {
                    $sponsor = $sponsors->get($order->metadata['sponsor_id']);
                }

                $sponsorAmount = (float) $order->commissions->where('type', 'sponsor')->sum('amount');
                $cashAmount = (float) $order->commissions->where('type', 'cash_pos')->sum('amount');
                $commission = $sponsorAmount + $cashAmount;

                $commissionParts = [];
                if ($sponsorAmount > 0) {
                    $commissionParts[] = 'Sponsor $'.number_format($sponsorAmount, 2);
                }
                if ($cashAmount > 0) {
                    $commissionParts[] = 'CASH $'.number_format($cashAmount, 2);
                }

                $price = (float) ($order->subtotal ?? 0);
                $pv = (float) $order->items->sum(function ($item) {
                    return ($item->pv_value ?? 0) * $item->quantity;
                });

                return [
                    'date' => $order->created_at->format('d/m/Y H:i'),
                    'order_number' => $order->order_number,
                    'client_name' => $order->user?->name ?? 'N/A',
                    'client_phone' => $order->user?->phone,
                    'item_kind' => $itemKind,
                    'item_label' => $itemLabel,
                    'item_extra' => $itemExtra,
                    'sponsor_code' => $sponsor?->sponsor_id,
                    'sponsor_name' => $sponsor?->name,
                    'price' => $price,
                    'commission' => $commission,
                    'commission_detail' => implode(' · ', $commissionParts),
                    'paid' => $price,
                    'pv' => $pv,
                ];
            });

            $totals = [
                'price' => (float) $rows->sum('price'),
                'commission' => (float) $rows->sum('commission'),
                'paid' => (float) $rows->sum('paid'),
                'pv' => (float) $rows->sum('pv'),
            ];

            $periodLabel = 'Toutes les périodes';
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $periodLabel = \Carbon\Carbon::parse($request->date_from)->format('d/m/Y')
                    .' – '.\Carbon\Carbon::parse($request->date_to)->format('d/m/Y');
            } elseif ($request->filled('date_from')) {
                $periodLabel = 'Depuis '.\Carbon\Carbon::parse($request->date_from)->format('d/m/Y');
            } elseif ($request->filled('date_to')) {
                $periodLabel = 'Jusqu\'au '.\Carbon\Carbon::parse($request->date_to)->format('d/m/Y');
            }

            return $this->downloadPdfView(
                'cashier.history-pdf',
                [
                    'rows' => $rows,
                    'totals' => $totals,
                    'periodLabel' => $periodLabel,
                    'search' => $request->input('search'),
                    'logoBase64' => $this->loadPdfLogoBase64(),
                    'date_generated' => now()->format('d/m/Y H:i'),
                ],
                'rapport_historique_ventes_pos_'.now()->format('Y-m-d').'.pdf',
                'a4',
                'landscape',
                300,
                '512M'
            );
        } catch (\Throwable $e) {
            $this->logPdfFailure('cashier.historyExportPdf', $e);

            return back()->with('error', 'Erreur lors de la génération du PDF. Réessayez ou réduisez la période.');
        }
    }

    /**
     * Ventes du jour
     */
    public function dailySales(Request $request)
    {
        $baseQuery = Order::whereDate('created_at', today())
            ->where('status', 'completed')
            ->whereIn('source', ['pos', 'membership']);

        $allToday = (clone $baseQuery)->with(['user.parrain'])->get();

        $listQuery = (clone $baseQuery)->with(['user.parrain'])->orderByDesc('created_at');
        if ($request->filled('search')) {
            $search = ltrim(trim((string) $request->search), '#');
            $listQuery->where('order_number', 'LIKE', "%{$search}%");
        }
        $sales = $listQuery->get();

        $sponsorIds = $sales
            ->map(fn ($sale) => data_get($sale->metadata, 'sponsor_id'))
            ->filter()
            ->unique()
            ->values();
        $sponsorsById = $sponsorIds->isEmpty()
            ? collect()
            : User::query()->whereIn('id', $sponsorIds)->get()->keyBy('id');

        $stats = [
            'total_orders' => $allToday->count(),
            'total_amount' => $allToday->sum('total'),
            'average_order' => $allToday->count() > 0 ? $allToday->sum('total') / $allToday->count() : 0,
            'pos_count' => $allToday->where('source', 'pos')->count(),
            'mlm_count' => $allToday->where('source', 'membership')->count(),
            'payment_methods' => $allToday->groupBy('payment_method')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('total'),
                ];
            }),
        ];

        return view('cashier.daily-sales', compact('sales', 'stats', 'sponsorsById'));
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
        
        $staffRoles = AdminCashierController::STAFF_ROLES;

        $sponsors = User::where('is_active', true)
            ->where('user_type', 'member')
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', $staffRoles))
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%")
                  ->orWhere('sponsor_id', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone', 'sponsor_id']);
        
        return response()->json($sponsors);
    }

    /**
     * Membres MLM visibles en caisse (hors employés staff).
     */
    protected function cashierMembersQuery()
    {
        $staffRoles = AdminCashierController::STAFF_ROLES;

        return User::query()
            ->where('user_type', 'member')
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', $staffRoles));
    }

    /**
     * Liste des membres
     */
    public function members(Request $request)
    {
        $query = $this->cashierMembersQuery();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('sponsor_id', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $members = $query
            ->with([
                'package',
                'parrain:id,sponsor_id',
                'genealogy.sponsor:id,sponsor_id',
            ])
            ->withSum([
                'commissions as pos_cash_paid_total' => function ($q) {
                    $q->where('source', 'pos')->where('type', 'cash_pos')->where('status', 'paid');
                },
            ], 'amount')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();
        
        $stats = [
            'total' => $this->cashierMembersQuery()->count(),
            'active' => $this->cashierMembersQuery()->where('is_active', true)->count(),
            'inactive' => $this->cashierMembersQuery()->where('is_active', false)->count(),
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
        $member = $this->cashierMembersQuery()
            ->with(['package', 'orders', 'parrain:id,sponsor_id,name,email,user_type', 'pvBalance'])
            ->findOrFail($id);
        
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
        
        $downlines = $this->cashierMembersQuery()
            ->where('parrain_id', $member->id)
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
            'active_downlines' => $this->cashierMembersQuery()
                ->where('parrain_id', $member->id)
                ->where('is_active', true)
                ->count(),
        ];
        
        $memberPv = $member->pvSummary();

        return view('cashier.members.show', compact('member', 'commissions', 'stats', 'downlines', 'memberPv'));
    }

    /**
     * Commandes d'un membre
     */
    public function memberOrders($id)
    {
        $member = $this->cashierMembersQuery()->findOrFail($id);
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
        $member = $this->cashierMembersQuery()->findOrFail($id);
        
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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('type', 'LIKE', "%{$search}%")
                    ->orWhere('source', 'LIKE', "%{$search}%")
                    ->orWhere('id', $search)
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('sponsor_id', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
            });
        }
        
        $commissions = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
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
        $excludedTypes = ['pos_transaction', 'purchase', 'new_client'];

        $commission = Commission::with([
            'user:id,name,sponsor_id,email,phone,is_active,user_type',
            'fromUser:id,name,sponsor_id',
            'order:id,order_number,total,subtotal,source,status,created_at,user_id',
            'order.user:id,name',
        ])->findOrFail($id);

        $member = $commission->user;
        $order = $commission->order;
        $saleTotal = (float) ($order?->total ?? $order?->subtotal ?? 0);
        $commissionAmount = (float) ($commission->amount ?? 0);
        $companyShare = $saleTotal > 0 ? max(0, $saleTotal - $commissionAmount) : null;

        $typeLabel = $commission->type === 'cash_pos'
            ? 'POS'
            : ($commission->type_label ?? ucfirst(str_replace('_', ' ', (string) $commission->type)));

        $similarCommissions = Commission::with(['order:id,order_number'])
            ->where('user_id', $commission->user_id)
            ->where('id', '!=', $id)
            ->whereNotIn('type', $excludedTypes)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $memberPaidTotal = Commission::where('user_id', $commission->user_id)
            ->where('status', 'paid')
            ->whereNotIn('type', $excludedTypes)
            ->sum('amount');

        return view('cashier.commissions-show', compact(
            'commission',
            'member',
            'order',
            'saleTotal',
            'commissionAmount',
            'companyShare',
            'typeLabel',
            'similarCommissions',
            'memberPaidTotal'
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
        $member = $this->cashierMembersQuery()->with(['package', 'parrain'])->findOrFail($id);
        
        $sponsor = null;
        if ($member->parrain_id) {
            $sponsor = User::find($member->parrain_id);
        }
        
        // Récupérer les métadonnées
        $metadata = $member->metadata ?? [];
        
        $data = [
            'member' => $member,
            'sponsor' => $sponsor,
            'metadata' => $metadata,
            'logoBase64' => $this->loadPdfLogoBase64(),
            'date' => now(),
        ];

        $filename = 'formulaire_adhesion_'.($member->sponsor_id ?: $member->id).'.pdf';

        return $this->downloadPdfView(
            'cashier.adhesion-pdf',
            $data,
            $filename,
            'a4',
            'portrait',
            180,
            '512M'
        );
    } catch (\Throwable $e) {
        $this->logPdfFailure('cashier.adhesion-pdf', $e, ['member_id' => $id]);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'message' => 'Erreur lors de la génération du PDF.',
            ], 500);
        }

        return redirect()->back()->with('error', 'Erreur lors de la génération du PDF.');
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