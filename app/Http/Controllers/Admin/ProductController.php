<?php
// app/Http/Controllers/Admin/ProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
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

        $products = $query->orderBy('id', 'asc')->paginate(15);
        
        $categories = Product::distinct()->pluck('category')->filter();
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'featured' => Product::where('is_featured', true)->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
            'low_stock' => Product::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
        ];

        return view('admin.products.index', compact('products', 'categories', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $categories = [
            'Informatique', 'Téléphonie', 'Audio', 'Tablette', 
            'Montres', 'Accessoires', 'Services', 'Santé', 'Beauté',
            'Maison', 'Jardin', 'Sports', 'Vêtements'
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
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:255|unique:products',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $data = $request->all();
        
        // ✅ Upload de l'image principale
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->put('products/' . $filename, file_get_contents($file));
            $data['image'] = $filename;
        }

        // ✅ Upload de la galerie
        if ($request->hasFile('gallery')) {
            $gallery = [];
            foreach ($request->file('gallery') as $file) {
                $filename = time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                Storage::disk('public')->put('products/' . $filename, file_get_contents($file));
                $gallery[] = $filename;
            }
            $data['gallery'] = $gallery;
        }

        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');

        $product = Product::create($data);

        return redirect()->route('admin.products')
            ->with('success', "🛍️ Produit '{$product->name}' créé avec succès.");
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = [
            'Informatique', 'Téléphonie', 'Audio', 'Tablette', 
            'Montres', 'Accessoires', 'Services', 'Santé', 'Beauté',
            'Maison', 'Jardin', 'Sports', 'Vêtements'
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
            'cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $id,
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $data = $request->all();
        
        // ✅ Upload de l'image principale
        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists('products/' . $product->image)) {
                Storage::disk('public')->delete('products/' . $product->image);
            }
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->put('products/' . $filename, file_get_contents($file));
            $data['image'] = $filename;
        }

        // ✅ Upload de la galerie
        if ($request->hasFile('gallery')) {
            $gallery = $product->gallery ?? [];
            foreach ($request->file('gallery') as $file) {
                $filename = time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                Storage::disk('public')->put('products/' . $filename, file_get_contents($file));
                $gallery[] = $filename;
            }
            $data['gallery'] = $gallery;
        }

        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');

        $product->update($data);

        return redirect()->route('admin.products')
            ->with('success', "🛍️ Produit '{$product->name}' mis à jour.");
    }

    /**
     * Supprimer un produit
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // ✅ Supprimer l'image principale
        if ($product->image && Storage::disk('public')->exists('products/' . $product->image)) {
            Storage::disk('public')->delete('products/' . $product->image);
        }
        
        // ✅ Supprimer la galerie
        if ($product->gallery) {
            foreach ($product->gallery as $image) {
                if (Storage::disk('public')->exists('products/' . $image)) {
                    Storage::disk('public')->delete('products/' . $image);
                }
            }
        }
        
        $name = $product->name;
        $product->delete();
        
        return redirect()->route('admin.products')
            ->with('success', "🗑️ Produit '{$name}' supprimé.");
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
            ->with('success', "🛍️ Produit '{$product->name}' {$status}.");
    }

    /**
     * Mettre en avant/retirer un produit
     */
    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->is_featured = !$product->is_featured;
        $product->save();
        
        $status = $product->is_featured ? 'mis en avant' : 'retiré des avant';
        return redirect()->route('admin.products')
            ->with('success', "🛍️ Produit '{$product->name}' {$status}.");
    }

    /**
     * Supprimer une image de la galerie
     */
    public function removeGalleryImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        $product = Product::findOrFail($id);
        
        if ($product->gallery) {
            $gallery = array_filter($product->gallery, function($img) use ($request) {
                return $img !== $request->image;
            });
            
            if (Storage::disk('public')->exists('products/' . $request->image)) {
                Storage::disk('public')->delete('products/' . $request->image);
            }
            
            $product->gallery = array_values($gallery);
            $product->save();
        }

        return response()->json(['success' => true]);
    }
}