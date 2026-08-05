<?php
// app/Http/Controllers/WishlistController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Afficher la liste de souhaits
     */
    public function index()
    {
        $user = Auth::user();
        $wishlist = Wishlist::where('user_id', $user->id)
            ->with('product')
            ->get();

        return view('wishlist.index', compact('wishlist'));
    }

    /**
     * Ajouter un produit à la liste de souhaits
     */
    public function add($productId)
    {
        $user = Auth::user();
        $product = Product::findOrFail($productId);

        $exists = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        if (!$exists) {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté à la liste de souhaits'
        ]);
    }

    /**
     * Ajouter ou retirer un produit (toggle)
     */
    public function toggle($productId)
    {
        $user = Auth::user();
        $product = Product::findOrFail($productId);

        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $message = 'Produit retiré de la liste de souhaits';
            $added = false;
        } else {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
            $message = 'Produit ajouté à la liste de souhaits';
            $added = true;
        }

        return response()->json([
            'success' => true,
            'added' => $added,
            'message' => $message
        ]);
    }

    /**
     * Retirer un produit de la liste de souhaits
     */
    public function remove($productId)
    {
        $user = Auth::user();

        Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produit retiré de la liste de souhaits'
        ]);
    }
}