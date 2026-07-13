<?php
// app/Http/Controllers/Admin/ProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function create()
    {
        $categories = [
            'Computers', 'Phones', 'Audio', 'Tablets',
            'Watches', 'Accessories', 'Services'
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

        return redirect()->route('admin.products')
            ->with('success', "Product '{$product->name}' created. PV: {$product->pv_value}, BV: {$product->bv_value}");
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = [
            'Computers', 'Phones', 'Audio', 'Tablets',
            'Watches', 'Accessories', 'Services'
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

        return redirect()->route('admin.products')
            ->with('success', "Product '{$product->name}' updated. PV: {$product->pv_value}, BV: {$product->bv_value}");
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && Storage::disk('public')->exists('products/' . $product->image)) {
            Storage::disk('public')->delete('products/' . $product->image);
        }

        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.products')
            ->with('success', "Product '{$name}' deleted.");
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();

        $status = $product->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.products')
            ->with('success', "Product '{$product->name}' {$status}.");
    }

    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->is_featured = !$product->is_featured;
        $product->save();

        $status = $product->is_featured ? 'featured' : 'unfeatured';
        return redirect()->route('admin.products')
            ->with('success', "Product '{$product->name}' {$status}.");
    }
}