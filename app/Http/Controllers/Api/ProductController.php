<?php
// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Liste des produits
     */
    public function index(Request $request)
    {
        $query = Product::where('is_active', true);
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        $products = $query->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Rechercher des produits
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $products = Product::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Produits en vedette
     */
    public function featured()
    {
        $products = Product::where('is_active', true)
            ->where('is_featured', true)
            ->limit(10)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Catégories de produits
     */
    public function categories()
    {
        $categories = Product::where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->filter();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Détails d'un produit
     */
    public function show($id)
    {
        $product = Product::where('is_active', true)->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }
}