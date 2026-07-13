<?php
// app/Http/Controllers/ProductController.php

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

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float)$request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float)$request->max_price);
        }

        if ($request->filled('min_pv')) {
            $query->where('pv_value', '>=', (int)$request->min_pv);
        }

        if ($request->filled('max_pv')) {
            $query->where('pv_value', '<=', (int)$request->max_pv);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(12);

        $categories = Product::where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        $pvRange = [
            'min' => Product::where('is_active', true)->min('pv_value') ?? 0,
            'max' => Product::where('is_active', true)->max('pv_value') ?? 50,
        ];

        return view('products.index', compact('products', 'categories', 'pvRange'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relatedProducts = Product::where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

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
            ->get(['id', 'name', 'slug', 'price', 'pv_value', 'bv_value', 'image', 'stock', 'description', 'category', 'is_featured']);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function apiProducts(Request $request)
    {
        $products = Product::where('is_active', true)
            ->when($request->filled('category'), function($query) use ($request) {
                return $query->where('category', $request->category);
            })
            ->when($request->filled('min_pv'), function($query) use ($request) {
                return $query->where('pv_value', '>=', (int)$request->min_pv);
            })
            ->when($request->filled('max_pv'), function($query) use ($request) {
                return $query->where('pv_value', '<=', (int)$request->max_pv);
            })
            ->select('id', 'name', 'slug', 'price', 'pv_value', 'bv_value', 'stock', 'image', 'category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function apiFeatured()
    {
        $products = Product::where('is_active', true)
            ->where('is_featured', true)
            ->limit(10)
            ->get(['id', 'name', 'slug', 'price', 'pv_value', 'bv_value', 'image']);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function apiCategories()
    {
        $categories = Product::where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}