<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_active', true);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%');
            });
        }
        
        $products = $query->orderBy('created_at', 'desc')->paginate(12);
        
        $categories = Product::where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();
        
        return view('products.index', compact('products', 'categories'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        $relatedProducts = Product::where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();
        
        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * API de recherche en temps réel
     */
    public function search(Request $request)
    {
        $search = $request->get('q', '');
        
        if (strlen($search) < 1) {
            return response()->json([]);
        }
        
        $products = Product::where('is_active', true)
            ->where(function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('category', 'like', '%' . $search . '%');
            })
            ->limit(20)
            ->get(['id', 'name', 'slug', 'price', 'image', 'stock', 'description', 'category', 'is_featured']);
        
        return response()->json($products);
    }
}
