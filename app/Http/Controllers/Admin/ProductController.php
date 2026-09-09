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
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('id', 'desc')->paginate(15);

        $categories = Product::distinct()->pluck('category')->filter()->values();

        // Statistiques adaptées à votre table
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'featured' => Product::where('is_featured', true)->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
            'low_stock' => Product::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
            'total_pv' => Product::sum('pv_value') ?? 0,
            // 'total_bv' est supprimé car la colonne n'existe pas
        ];

        return view('admin.products.index', compact('products', 'categories', 'stats'));
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

        // Génération automatique du QR code
        $this->generateQrCodeForProduct($product);

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

        $product->update($data);

        // Régénérer le QR code
        $this->generateQrCodeForProduct($product);

        return redirect()->route('admin.products')
            ->with('success', "Produit '{$product->name}' mis à jour avec succès !");
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

        // Supprimer le QR code
        if (isset($product->metadata['qr_code_svg'])) {
            $qrPath = $product->metadata['qr_code_svg'];
            if (Storage::disk('public')->exists($qrPath)) {
                Storage::disk('public')->delete($qrPath);
            }
        }

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
        return redirect()->route('admin.products')
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
        return redirect()->route('admin.products')
            ->with('success', "Produit '{$product->name}' {$status} avec succès !");
    }

    // ============================================================
    // MÉTHODES POUR LES QR CODES
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

            // Générer le QR code en SVG
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
     * Générer tous les QR codes
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
     * Afficher la liste des QR codes
     */
    public function showQrCodes()
    {
        $products = Product::where('is_active', true)->get();
        return view('admin.products.qr-codes-list', compact('products'));
    }

    /**
     * Afficher le QR code d'un produit spécifique
     */
    public function showQrCode($id)
    {
        $product = Product::findOrFail($id);
        
        // Générer le QR code si nécessaire
        if (!isset($product->metadata['qr_code_svg']) || 
            !Storage::disk('public')->exists($product->metadata['qr_code_svg'])) {
            $this->generateQrCodeForProduct($product);
            $product->refresh();
        }

        return view('admin.products.qr-code-single', compact('product'));
    }

    /**
     * Télécharger le QR code d'un produit
     */
    public function downloadQrCode($id)
    {
        $product = Product::findOrFail($id);
        
        // Générer si nécessaire
        if (!isset($product->metadata['qr_code_svg']) || 
            !Storage::disk('public')->exists($product->metadata['qr_code_svg'])) {
            $this->generateQrCodeForProduct($product);
            $product->refresh();
        }

        $qrPath = $product->metadata['qr_code_svg'] ?? null;
        
        if ($qrPath && Storage::disk('public')->exists($qrPath)) {
            return response()->download(storage_path('app/public/' . $qrPath));
        }

        // Fallback: générer à la volée
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

    /**
     * Imprimer toutes les étiquettes QR
     */
    public function printAllQrCodes()
    {
        $products = Product::where('is_active', true)->get();
        return view('admin.products.qr-codes-print', compact('products'));
    }
}