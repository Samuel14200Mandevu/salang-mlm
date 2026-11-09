<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductBarcodeService;
use Illuminate\Console\Command;

class GenerateProductBarcodes extends Command
{
    protected $signature = 'products:generate-barcodes {--all : Inclure aussi les produits inactifs}';

    protected $description = 'Génère des codes-barres Code 128 (SKU) pour les produits';

    public function handle(ProductBarcodeService $barcodeService): int
    {
        $query = Product::query()->orderBy('id');
        if (! $this->option('all')) {
            $query->where('is_active', true);
        }

        $products = $query->get();
        $count = 0;

        $this->info('Génération des codes-barres Code 128…');

        foreach ($products as $product) {
            if ($barcodeService->generate($product)) {
                $count++;
                $this->line("OK #{$product->id} {$product->name} → {$product->fresh()->sku}");
            } else {
                $this->error("Échec #{$product->id} {$product->name}");
            }
        }

        $this->info("{$count} code(s)-barres généré(s).");

        return self::SUCCESS;
    }
}
