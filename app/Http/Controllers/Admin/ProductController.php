<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        // Trier par ID croissant (du plus petit au plus grand)
        $products = Product::orderBy('id', 'asc')->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = [
            'Informatique', 'Téléphonie', 'Audio', 'Tablette', 
            'Montres', 'Accessoires', 'Services', 'Santé', 'Beauté'
        ];
        return view('admin.products.create', compact('categories'));
    }

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
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $data = $request->all();
        
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            
            $content = file_get_contents($file->getRealPath());
            Storage::disk('public')->put('products/' . $filename, $content);
            
            if (Storage::disk('public')->exists('products/' . $filename)) {
                $data['image'] = $filename;
            }
        }

        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');

        Product::create($data);

        return redirect()->route('admin.products')->with('success', '🛍️ Produit créé avec succès.');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = [
            'Informatique', 'Téléphonie', 'Audio', 'Tablette', 
            'Montres', 'Accessoires', 'Services', 'Santé', 'Beauté'
        ];
        return view('admin.products.edit', compact('product', 'categories'));
    }

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
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $data = $request->all();
        
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            
            if ($product->image && Storage::disk('public')->exists('products/' . $product->image)) {
                Storage::disk('public')->delete('products/' . $product->image);
            }
            
            $content = file_get_contents($file->getRealPath());
            Storage::disk('public')->put('products/' . $filename, $content);
            
            if (Storage::disk('public')->exists('products/' . $filename)) {
                $data['image'] = $filename;
            }
        }

        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');

        $product->update($data);

        return redirect()->route('admin.products')->with('success', '🛍️ Produit mis à jour.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        if ($product->image && Storage::disk('public')->exists('products/' . $product->image)) {
            Storage::disk('public')->delete('products/' . $product->image);
        }
        
        $product->delete();
        
        return redirect()->route('admin.products')->with('success', '🗑️ Produit supprimé.');
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();
        
        $status = $product->is_active ? 'activé' : 'désactivé';
        return redirect()->route('admin.products')->with('success', "🛍️ Produit {$status} avec succès.");
    }
}
