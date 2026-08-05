<?php
// app/Http/Controllers/Admin/ProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProductController extends Controller
{
    private function getDefaultPV(): int
    {
        return config('product.default_pv', 10);
    }

    private function getDefaultBV(): int
    {
        return config('product.default_bv', 10);
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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        }

        $products = $query->orderBy('id', 'desc')->paginate(15);

        $categories = Product::distinct()->pluck('category')->filter();
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'featured' => Product::where('is_featured', true)->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
            'low_stock' => Product::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
            'total_pv' => Product::sum('pv_value'),
            'total_bv' => Product::sum('bv_value'),
        ];

        return view('admin.products.index', compact('products', 'categories', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $categories = [
            'Computers', 'Phones', 'Audio', 'Tablets',
            'Watches', 'Accessories', 'Services'
        ];
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
            'pv_value' => 'nullable|integer|min:0|max:1000',
            'bv_value' => 'nullable|integer|min:0|max:1000',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:255|unique:products',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $data = $request->all();

        $data['pv_value'] = $request->pv_value ?? $this->getDefaultPV();
        $data['bv_value'] = $request->bv_value ?? $this->getDefaultBV();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/products', $filename);
            $data['image'] = $filename;
        }

        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');

        $product = Product::create($data);

        // ✅ Générer automatiquement le QR code après création
        $this->generateQrCodeForProduct($product);

        return redirect()->route('admin.products')
            ->with('success', "Product '{$product->name}' created. PV: {$product->pv_value}, BV: {$product->bv_value}");
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = [
            'Computers', 'Phones', 'Audio', 'Tablets',
            'Watches', 'Accessories', 'Services'
        ];
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
            'pv_value' => 'nullable|integer|min:0|max:1000',
            'bv_value' => 'nullable|integer|min:0|max:1000',
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $id,
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $data = $request->all();

        $data['pv_value'] = $request->pv_value ?? $this->getDefaultPV();
        $data['bv_value'] = $request->bv_value ?? $this->getDefaultBV();

        if ($request->hasFile('image')) {
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

        $product->update($data);

        // ✅ Régénérer le QR code si le produit a été mis à jour
        $this->generateQrCodeForProduct($product);

        return redirect()->route('admin.products')
            ->with('success', "Product '{$product->name}' updated. PV: {$product->pv_value}, BV: {$product->bv_value}");
    }

    /**
     * Supprimer un produit
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && Storage::disk('public')->exists('products/' . $product->image)) {
            Storage::disk('public')->delete('products/' . $product->image);
        }

        // ✅ Supprimer le QR code si existe
        if (isset($product->metadata['qr_code_svg'])) {
            $qrPath = $product->metadata['qr_code_svg'];
            if (Storage::disk('public')->exists($qrPath)) {
                Storage::disk('public')->delete($qrPath);
            }
        }

        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.products')
            ->with('success', "Product '{$name}' deleted.");
    }

    /**
     * Activer/Désactiver un produit
     */
    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();

        $status = $product->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.products')
            ->with('success', "Product '{$product->name}' {$status}.");
    }

    /**
     * Mettre en vedette/Retirer la vedette
     */
    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->is_featured = !$product->is_featured;
        $product->save();

        $status = $product->is_featured ? 'featured' : 'unfeatured';
        return redirect()->route('admin.products')
            ->with('success', "Product '{$product->name}' {$status}.");
    }

    // ============================================================
    // ✅ MÉTHODES POUR LES QR CODES
    // ============================================================

    /**
     * Générer un QR code pour un produit
     */
    private function generateQrCodeForProduct(Product $product)
    {
        try {
            // Créer le dossier si nécessaire
            if (!Storage::disk('public')->exists('qr_codes')) {
                Storage::disk('public')->makeDirectory('qr_codes');
            }

            // Générer le QR code en SVG (pas besoin d'Imagick)
            $qrCode = QrCode::format('svg')
                ->size(300)
                ->color(14, 47, 118)
                ->backgroundColor(255, 255, 255)
                ->margin(1)
                ->generate((string)$product->id);

            // Sauvegarder le fichier
            $filename = 'qr_codes/product_' . $product->id . '.svg';
            Storage::disk('public')->put($filename, $qrCode);

            // Mettre à jour les métadonnées
            $metadata = $product->metadata ?? [];
            $metadata['qr_code_svg'] = $filename;
            $metadata['qr_content'] = (string)$product->id;
            $metadata['qr_base64'] = base64_encode($qrCode);
            $product->metadata = $metadata;
            $product->save();

            return true;
        } catch (\Exception $e) {
            \Log::error('Erreur génération QR code: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Générer les QR codes pour tous les produits actifs
     */
    public function generateAllQrCodes()
    {
        $products = Product::where('is_active', true)->get();
        $generated = 0;
        $errors = [];

        foreach ($products as $product) {
            if ($this->generateQrCodeForProduct($product)) {
                $generated++;
            } else {
                $errors[] = "Produit #{$product->id}: {$product->name}";
            }
        }

        $message = "✅ {$generated} QR codes générés avec succès !";
        if (!empty($errors)) {
            $message .= " ❌ Erreurs pour: " . implode(', ', $errors);
        }

        return redirect()->route('admin.products')
            ->with('success', $message);
    }

    /**
     * Afficher tous les QR codes
     */
    public function showQrCodes()
    {
        $products = Product::where('is_active', true)->get();
        return view('admin.products.qr-codes-list', compact('products'));
    }

    /**
     * Imprimer toutes les étiquettes QR
     */
    public function printAllQrCodes()
    {
        $products = Product::where('is_active', true)->get();
        return view('admin.products.qr-codes-print', compact('products'));
    }

    /**
     * Afficher le QR code d'un produit
     */
    public function showQrCode($id)
    {
        $product = Product::findOrFail($id);
        
        // Générer le QR code en direct
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->color(14, 47, 118)
            ->backgroundColor(255, 255, 255)
            ->margin(1)
            ->generate((string)$product->id);

        return view('admin.products.qr-code-single', compact('product', 'qrCode'));
    }

    /**
     * Télécharger le QR code d'un produit
     */
    public function downloadQrCode($id)
    {
        $product = Product::findOrFail($id);
        $qrPath = $product->metadata['qr_code_svg'] ?? null;
        
        if (!$qrPath || !Storage::disk('public')->exists($qrPath)) {
            // Générer le QR code à la volée
            $qrCode = QrCode::format('svg')
                ->size(300)
                ->color(14, 47, 118)
                ->backgroundColor(255, 255, 255)
                ->margin(1)
                ->generate((string)$product->id);
            
            return response($qrCode)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Content-Disposition', 'attachment; filename="qr_' . $product->id . '.svg"');
        }

        return response()->download(storage_path('app/public/' . $qrPath));
    }

    /**
     * Générer un QR code pour un produit spécifique (via admin)
     */
    public function generateQrCode($id)
    {
        $product = Product::findOrFail($id);
        
        if ($this->generateQrCodeForProduct($product)) {
            return redirect()->route('admin.products.qr-code', $id)
                ->with('success', "QR code généré pour '{$product->name}'");
        }

        return redirect()->back()
            ->with('error', "Erreur lors de la génération du QR code pour '{$product->name}'");
    }
    /**
    * Générer tous les QR codes (via POST)
    */
    public function generateQrCodesBatch(Request $request)
    {
    $products = Product::where('is_active', true)->get();
    $generated = 0;
    $errors = [];

    foreach ($products as $product) {
        if ($this->generateQrCodeForProduct($product)) {
            $generated++;
        } else {
            $errors[] = "Produit #{$product->id}: {$product->name}";
        }
    }

    $message = " {$generated} QR codes générés avec succès !";
    if (!empty($errors)) {
        $message .= " Erreurs pour: " . implode(', ', $errors);
    }

    return redirect()->route('admin.products')->with('success', $message);
    }
}