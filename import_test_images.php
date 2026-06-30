<?php

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

// Activer l'environnement Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== IMPORTATION DES IMAGES DE TEST ===\n\n";

// Images de test à télécharger
$images = [
    'iphone-14-pro' => 'https://via.placeholder.com/300x300/1a1a2e/ffffff?text=iPhone+14+Pro',
    'iphone-15-pro' => 'https://via.placeholder.com/300x300/2d4059/ffffff?text=iPhone+15+Pro',
    'iphone-15' => 'https://via.placeholder.com/300x300/3b5998/ffffff?text=iPhone+15',
    'macbook-pro' => 'https://via.placeholder.com/300x300/4a4a4a/ffffff?text=MacBook+Pro',
    'airpods-pro' => 'https://via.placeholder.com/300x300/5d5d5d/ffffff?text=AirPods+Pro',
    'apple-watch' => 'https://via.placeholder.com/300x300/6d6d6d/ffffff?text=Apple+Watch',
];

foreach ($images as $name => $url) {
    echo "Téléchargement de $name... ";
    
    $content = file_get_contents($url);
    if ($content) {
        $filename = $name . '.jpg';
        Storage::disk('public')->put('products/' . $filename, $content);
        echo "✅ OK ($filename)\n";
        
        // Mettre à jour le produit correspondant
        $product = Product::where('name', 'like', "%$name%")->first();
        if ($product) {
            $product->image = $filename;
            $product->save();
            echo "   → Produit mis à jour: {$product->name}\n";
        }
    } else {
        echo "❌ ÉCHEC\n";
    }
}

echo "\n=== TERMINÉ ===\n";
