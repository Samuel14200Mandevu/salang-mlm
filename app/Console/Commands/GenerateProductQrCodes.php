<?php
// app/Console/Commands/GenerateProductQrCodes.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class GenerateProductQrCodes extends Command
{
    protected $signature = 'products:generate-qr-codes';
    protected $description = 'Génère des QR codes pour tous les produits actifs';

    public function handle()
    {
        $products = Product::where('is_active', true)->get();
        $count = 0;

        $this->info('🔄 Génération des QR codes en SVG...');

        // Créer le dossier si nécessaire
        if (!Storage::disk('public')->exists('qr_codes')) {
            Storage::disk('public')->makeDirectory('qr_codes');
        }

        foreach ($products as $product) {
            try {
                // ✅ UTILISER LE FORMAT SVG (PAS BESOIN D'IMAGICK)
                $qrCode = QrCode::format('svg')
                    ->size(300)
                    ->color(14, 47, 118)
                    ->backgroundColor(255, 255, 255)
                    ->generate((string)$product->id);

                // Sauvegarder en SVG
                $filename = 'qr_codes/product_' . $product->id . '.svg';
                Storage::disk('public')->put($filename, $qrCode);

                // Sauvegarder aussi en PNG si GD est disponible
                $pngPath = null;
                if (extension_loaded('gd')) {
                    try {
                        $pngCode = QrCode::format('png')
                            ->size(300)
                            ->color(14, 47, 118)
                            ->backgroundColor(255, 255, 255)
                            ->generate((string)$product->id);
                        
                        $pngFilename = 'qr_codes/product_' . $product->id . '.png';
                        Storage::disk('public')->put($pngFilename, $pngCode);
                        $pngPath = $pngFilename;
                    } catch (\Exception $e) {
                        $this->warn("⚠️ PNG non généré pour {$product->name} (GD non disponible)");
                    }
                }

                // Mettre à jour les métadonnées
                $metadata = $product->metadata ?? [];
                $metadata['qr_code_svg'] = $filename;
                $metadata['qr_code_png'] = $pngPath;
                $metadata['qr_content'] = (string)$product->id;
                $product->metadata = $metadata;
                $product->save();

                $count++;
                $this->info("✅ QR code généré pour: {$product->name} (ID: {$product->id})");
            } catch (\Exception $e) {
                $this->error("❌ Erreur pour {$product->name}: " . $e->getMessage());
            }
        }

        $this->info("🎉 {$count} QR codes générés avec succès !");
        $this->info("📁 Dossier: storage/app/public/qr_codes/");
        $this->info("📄 Format: SVG (compatible avec tous les navigateurs)");
    }
}