<?php
// app/Http/Controllers/Admin/ProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\ProductBarcodeService;
use App\Services\ProductStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(
        protected ProductStockService $stockService,
        protected ProductBarcodeService $barcodeService,
    ) {
    }

    private function getDefaultPV(): int
    {
        return config('product.default_pv', 10);
    }

    /**
     * Liste des produits
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('stock')) {
            match ($request->stock) {
                'out' => $query->where('stock', '<=', 0),
                'low' => $query->where('stock', '>', 0)->where('stock', '<=', 5),
                'ok' => $query->where('stock', '>', 5),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $categories = Product::distinct()->pluck('category')->filter()->values();

        // Statistiques adaptées à votre table
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();

        $stats = [
            'total' => $totalProducts,
            'active' => $activeProducts,
            'inactive' => max(0, $totalProducts - $activeProducts),
            'featured' => Product::where('is_featured', true)->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
            'low_stock' => Product::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
            'total_pv' => Product::sum('pv_value') ?? 0,
        ];

        return view('admin.products.index', compact('products', 'categories', 'stats'));
    }

    /**
     * Fiche détail produit (actions + code-barres).
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $recentMovements = StockMovement::query()
            ->where('product_id', $product->id)
            ->with('user:id,name')
            ->latest()
            ->limit(10)
            ->get();

        $hasBarcode = $this->barcodeService->hasBarcode($product);

        return view('admin.products.show', compact('product', 'recentMovements', 'hasBarcode'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        // Récupérer les catégories existantes dans la table
        $categories = Product::distinct()->pluck('category')->filter()->values()->toArray();
        
        // Catégories par défaut si aucune n'existe
        if (empty($categories)) {
            $categories = [
                'Thés', 
                'Compléments alimentaires', 
                'Gélules', 
                'Crèmes', 
                'Gouttes',
                'Comprimés'
            ];
        }
        
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Créer un produit
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'half_price' => 'nullable|numeric|min:0',
            'half_pv' => 'nullable|integer|min:0',
            'full_price' => 'nullable|numeric|min:0',
            'full_pv' => 'nullable|integer|min:0',
            'pv_value' => 'nullable|integer|min:0|max:1000',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:255|unique:products',
            'category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'packaging' => 'nullable|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['pv_value'] = $request->pv_value ?? $this->getDefaultPV();
        
        // Gestion de l'image
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/products', $filename);
            $data['image'] = $filename;
        }

        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');

        $product = Product::create($data);

        $this->stockService->recordInitial($product, (int) $product->stock, Auth::user());

        $this->barcodeService->ensureSku($product->fresh());
        $this->barcodeService->generate($product->fresh());

        return redirect()->route('admin.products')
            ->with('success', "Produit '{$product->name}' créé avec succès !");
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        
        $categories = Product::distinct()->pluck('category')->filter()->values()->toArray();
        
        if (empty($categories)) {
            $categories = [
                'Thés', 
                'Compléments alimentaires', 
                'Gélules', 
                'Crèmes', 
                'Gouttes',
                'Comprimés'
            ];
        }
        
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'half_price' => 'nullable|numeric|min:0',
            'half_pv' => 'nullable|integer|min:0',
            'full_price' => 'nullable|numeric|min:0',
            'full_pv' => 'nullable|integer|min:0',
            'pv_value' => 'nullable|integer|min:0|max:1000',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $id,
            'category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'packaging' => 'nullable|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['pv_value'] = $request->pv_value ?? $this->getDefaultPV();

        // Gestion de l'image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($product->image && Storage::disk('public')->exists('products/' . $product->image)) {
                Storage::disk('public')->delete('products/' . $product->image);
            }
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/products', $filename);
            $data['image'] = $filename;
        }

        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');

        $oldStock = (int) $product->stock;
        $newStock = (int) $request->input('stock', $oldStock);
        unset($data['stock']);

        $product->update($data);

        if ($newStock !== $oldStock) {
            try {
                $this->stockService->adjustTo(
                    $product->fresh(),
                    $newStock,
                    'Modification fiche produit',
                    Auth::user()
                );
            } catch (\Throwable $e) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $e->getMessage());
            }
        }

        $this->barcodeService->generate($product->fresh());

        return redirect()
            ->route('admin.products.show', $product->id)
            ->with('success', "Produit '{$product->name}' mis à jour avec succès !");
    }

    /**
     * Vue stock multi-produits (catalogue → bouton Stock).
     */
    public function stockOverview(Request $request)
    {
        $query = Product::query()->orderBy('name');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stock')) {
            match ($request->stock) {
                'out' => $query->where('stock', '<=', 0),
                'low' => $query->where('stock', '>', 0)->where('stock', '<=', 5),
                'ok' => $query->where('stock', '>', 5),
                default => null,
            };
        }

        // Par défaut (1re visite) : actifs seulement. Sinon suit le paramètre.
        $activeOnly = $request->exists('active_only')
            ? $request->boolean('active_only')
            : true;

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        $products = $query->get(['id', 'name', 'sku', 'category', 'stock', 'is_active', 'image']);

        $lowStock = Product::query()->where('is_active', true)->where('stock', '>', 0)->where('stock', '<=', 5)->count();
        $outOfStock = Product::query()->where('is_active', true)->where('stock', '<=', 0)->count();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'units' => (int) Product::where('is_active', true)->sum('stock'),
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'low' => $lowStock,
            'out' => $outOfStock,
            'listed' => $products->count(),
        ];

        return view('admin.products.stock-overview', compact('products', 'stats'));
    }

    /**
     * Historique global des mouvements de stock.
     */
    public function stockHistory(Request $request)
    {
        $query = StockMovement::query()
            ->with([
                'product:id,name,sku',
                'user:id,name',
            ])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $movements = $query->paginate(30)->withQueryString();
        $typeLabels = StockMovement::typeLabels();

        return view('admin.products.stock-history', compact('movements', 'typeLabels'));
    }

    /**
     * Appliquer plusieurs ajustements de stock en une fois.
     */
    public function bulkUpdateStock(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:entry,exit,adjust',
            'reason' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'nullable|integer|min:0',
        ]);

        $action = $validated['action'];
        $reason = trim((string) ($validated['reason'] ?? ''));
        $defaultReason = match ($action) {
            'entry' => 'Entrée stock (lot)',
            'exit' => 'Sortie stock (lot)',
            default => 'Inventaire multi-produits',
        };
        $reason = $reason !== '' ? $reason : $defaultReason;
        $actor = Auth::user();

        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($validated['items'] as $row) {
            $qtyRaw = $row['quantity'] ?? null;
            if ($qtyRaw === null || $qtyRaw === '') {
                $skipped++;
                continue;
            }

            $qty = (int) $qtyRaw;
            $product = Product::find($row['product_id']);
            if (! $product) {
                continue;
            }

            if (in_array($action, ['entry', 'exit'], true) && $qty < 1) {
                $skipped++;
                continue;
            }

            if ($action === 'adjust' && $qty === (int) $product->stock) {
                $skipped++;
                continue;
            }

            try {
                match ($action) {
                    'entry' => $this->stockService->receive($product, $qty, $reason, $actor),
                    'exit' => $this->stockService->remove($product, $qty, $reason, $actor),
                    'adjust' => $this->stockService->adjustTo($product, $qty, $reason, $actor),
                };
                $updated++;
            } catch (\Throwable $e) {
                $errors[] = $product->name.': '.$e->getMessage();
            }
        }

        if ($updated === 0 && empty($errors)) {
            return redirect()
                ->route('admin.products.stock-overview', $request->only(['search', 'stock', 'active_only']))
                ->with('error', 'Aucune quantité renseignée à appliquer.');
        }

        $message = "{$updated} produit(s) mis à jour.";
        if (! empty($errors)) {
            $message .= ' Erreurs : '.implode(' · ', array_slice($errors, 0, 5));
        }

        return redirect()
            ->route('admin.products.stock-overview', $request->only(['search', 'stock', 'active_only']))
            ->with(empty($errors) ? 'success' : 'error', $message);
    }

    /**
     * Fiche stock : journal + entrée / sortie / ajustement.
     */
    public function stock($id)
    {
        $product = Product::findOrFail($id);
        $movements = StockMovement::query()
            ->where('product_id', $product->id)
            ->with('user:id,name')
            ->latest()
            ->paginate(25);

        return view('admin.products.stock', compact('product', 'movements'));
    }

    /**
     * Appliquer un mouvement de stock manuel.
     */
    public function updateStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:entry,exit,adjust',
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $qty = (int) $validated['quantity'];
        $reason = trim((string) ($validated['reason'] ?? ''));
        $actor = Auth::user();

        if (in_array($validated['action'], ['entry', 'exit'], true) && $qty < 1) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Indiquez une quantité d’au moins 1.');
        }

        try {
            match ($validated['action']) {
                'entry' => $this->stockService->receive(
                    $product,
                    $qty,
                    $reason !== '' ? $reason : 'Entrée de stock',
                    $actor
                ),
                'exit' => $this->stockService->remove(
                    $product,
                    $qty,
                    $reason !== '' ? $reason : 'Sortie de stock',
                    $actor
                ),
                'adjust' => $this->stockService->adjustTo(
                    $product,
                    $qty,
                    $reason !== '' ? $reason : 'Ajustement inventaire',
                    $actor
                ),
            };
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.products.stock', $product->id)
            ->with('success', 'Stock mis à jour.');
    }

    /**
     * Supprimer un produit
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Supprimer l'image
        if ($product->image && Storage::disk('public')->exists('products/' . $product->image)) {
            Storage::disk('public')->delete('products/' . $product->image);
        }

        $this->barcodeService->deleteFiles($product);

        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.products')
            ->with('success', "Produit '{$name}' supprimé avec succès !");
    }

    /**
     * Activer/Désactiver un produit
     */
    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();

        $status = $product->is_active ? 'activé' : 'désactivé';

        return redirect()
            ->route('admin.products.show', $product->id)
            ->with('success', "Produit '{$product->name}' {$status} avec succès !");
    }

    /**
     * Mettre en vedette/Retirer la vedette
     */
    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->is_featured = !$product->is_featured;
        $product->save();

        $status = $product->is_featured ? 'mis en vedette' : 'retiré de la vedette';

        return redirect()
            ->route('admin.products.show', $product->id)
            ->with('success', "Produit '{$product->name}' {$status} avec succès !");
    }

    /**
     * Générer / régénérer le code-barres d’un produit.
     */
    public function generateBarcode($id)
    {
        $product = Product::findOrFail($id);

        if ($this->barcodeService->generate($product)) {
            return redirect()
                ->route('admin.products.show', $product->id)
                ->with('success', "Code-barres généré pour « {$product->name} » (SKU {$product->fresh()->sku}).");
        }

        return redirect()
            ->route('admin.products.show', $product->id)
            ->with('error', 'Impossible de générer le code-barres.');
    }

    /** @deprecated Alias routes QR → code-barres */
    public function generateQrCode($id)
    {
        return $this->generateBarcode($id);
    }

    // ============================================================
    // CODE-BARRES (scanner POS)
    // ============================================================

    public function generateAllBarcodes()
    {
        $products = Product::where('is_active', true)->get();
        $generated = 0;
        $errors = [];

        foreach ($products as $product) {
            if ($this->barcodeService->generate($product)) {
                $generated++;
            } else {
                $errors[] = "Produit #{$product->id}: {$product->name}";
            }
        }

        $message = "{$generated} code(s)-barres généré(s) avec succès.";
        if (! empty($errors)) {
            $message .= ' Erreurs : '.implode(', ', $errors);
        }

        return redirect()->route('admin.products')
            ->with('success', $message);
    }

    /** @deprecated */
    public function generateAllQrCodes()
    {
        return $this->generateAllBarcodes();
    }

    /** @deprecated */
    public function generateQrCodesBatch()
    {
        return $this->generateAllBarcodes();
    }

    public function showBarcodes()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.barcodes-list', compact('products'));
    }

    /** @deprecated */
    public function showQrCodes()
    {
        return $this->showBarcodes();
    }

    public function showBarcode($id)
    {
        $product = Product::findOrFail($id);
        $barcodeSvg = $this->barcodeService->svgFor($product);
        $product->refresh();

        return view('admin.products.barcode-single', [
            'product' => $product,
            'barcodeSvg' => $barcodeSvg,
        ]);
    }

    /** @deprecated */
    public function showQrCode($id)
    {
        return $this->showBarcode($id);
    }

    public function downloadBarcode($id)
    {
        $product = Product::findOrFail($id);
        $this->barcodeService->generate($product);
        $product->refresh();

        $path = $product->metadata['barcode_svg'] ?? null;

        if ($path && Storage::disk('public')->exists($path)) {
            return response()->download(
                storage_path('app/public/'.$path),
                'barcode_'.$product->sku.'.svg'
            );
        }

        $svg = $this->barcodeService->svgFor($product);

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="barcode_'.($product->sku ?: $product->id).'.svg"');
    }

    /** @deprecated */
    public function downloadQrCode($id)
    {
        return $this->downloadBarcode($id);
    }

    public function printAllBarcodes()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();

        foreach ($products as $product) {
            if (! $this->barcodeService->hasBarcode($product)) {
                $this->barcodeService->generate($product);
            }
        }

        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.barcodes-print', compact('products'));
    }

    /** @deprecated */
    public function printAllQrCodes()
    {
        return $this->printAllBarcodes();
    }
}