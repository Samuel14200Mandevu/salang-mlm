<?php

namespace App\Services;

use App\Models\Product;
use Com\Tecnick\Barcode\Barcode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductBarcodeService
{
    public const TYPE = 'C128';

    /**
     * Garantit un SKU unique scannable (Code 128).
     */
    public function ensureSku(Product $product): string
    {
        $sku = trim((string) $product->sku);

        if ($sku !== '') {
            return $sku;
        }

        $sku = 'SAL-'.str_pad((string) $product->id, 6, '0', STR_PAD_LEFT);

        while (
            Product::query()
                ->where('sku', $sku)
                ->where('id', '!=', $product->id)
                ->exists()
        ) {
            $sku = 'SAL-'.$product->id.'-'.substr(uniqid(), -4);
        }

        $product->sku = $sku;
        $product->save();

        return $sku;
    }

    /**
     * Génère et persiste le SVG code-barres (Code 128 = SKU).
     */
    public function generate(Product $product): bool
    {
        try {
            $sku = $this->ensureSku($product);

            if (! Storage::disk('public')->exists('barcodes')) {
                Storage::disk('public')->makeDirectory('barcodes');
            }

            $svg = $this->renderSvg($sku);

            $filename = 'barcodes/product_'.$product->id.'.svg';
            Storage::disk('public')->put($filename, $svg);

            $metadata = $product->metadata ?? [];
            $metadata['barcode_svg'] = $filename;
            $metadata['barcode_content'] = $sku;
            $metadata['barcode_type'] = self::TYPE;
            $metadata['barcode_base64'] = base64_encode($svg);

            // Nettoyage ancien QR si présent
            if (! empty($metadata['qr_code_svg']) && Storage::disk('public')->exists($metadata['qr_code_svg'])) {
                Storage::disk('public')->delete($metadata['qr_code_svg']);
            }
            unset($metadata['qr_code_svg'], $metadata['qr_base64'], $metadata['qr_content'], $metadata['qr_code_png']);

            $product->metadata = $metadata;
            $product->save();

            return true;
        } catch (\Throwable $e) {
            Log::error('Erreur génération code-barres: '.$e->getMessage(), [
                'product_id' => $product->id,
            ]);

            return false;
        }
    }

    public function renderSvg(string $code): string
    {
        $barcode = new Barcode;
        $obj = $barcode->getBarcodeObj(
            self::TYPE,
            $code,
            -2,
            -70,
            'black',
            [8, 8, 8, 8]
        )->setBackgroundColor('white');

        return $obj->getInlineSvgCode();
    }

    public function hasBarcode(Product $product): bool
    {
        $path = $product->metadata['barcode_svg'] ?? null;

        return is_string($path) && Storage::disk('public')->exists($path);
    }

    public function svgFor(Product $product): ?string
    {
        if (! $this->hasBarcode($product)) {
            if (! $this->generate($product)) {
                return null;
            }
            $product->refresh();
        }

        $path = $product->metadata['barcode_svg'] ?? null;
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->get($path);
        }

        if (! empty($product->metadata['barcode_base64'])) {
            return base64_decode((string) $product->metadata['barcode_base64']) ?: null;
        }

        $sku = $this->ensureSku($product);

        return $this->renderSvg($sku);
    }

    public function deleteFiles(Product $product): void
    {
        $metadata = $product->metadata ?? [];

        foreach (['barcode_svg', 'qr_code_svg', 'qr_code_png'] as $key) {
            $path = $metadata[$key] ?? null;
            if (is_string($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
