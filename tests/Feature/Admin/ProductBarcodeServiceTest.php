<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Services\ProductBarcodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductBarcodeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_sku_and_generates_code128_svg(): void
    {
        Storage::fake('public');

        $product = Product::create([
            'name' => 'Test Barcode',
            'slug' => 'test-barcode',
            'price' => 10,
            'stock' => 5,
            'is_active' => true,
            'sku' => null,
        ]);

        $service = app(ProductBarcodeService::class);

        $this->assertTrue($service->generate($product));

        $product->refresh();

        $this->assertSame('SAL-'.str_pad((string) $product->id, 6, '0', STR_PAD_LEFT), $product->sku);
        $this->assertTrue($service->hasBarcode($product));
        $this->assertSame($product->sku, $product->metadata['barcode_content'] ?? null);
        $this->assertSame('C128', $product->metadata['barcode_type'] ?? null);

        $svg = $service->svgFor($product);
        $this->assertNotEmpty($svg);
        $this->assertStringContainsString('<svg', $svg);
    }

    public function test_it_uses_existing_sku_as_barcode_content(): void
    {
        Storage::fake('public');

        $product = Product::create([
            'name' => 'EAN Product',
            'slug' => 'ean-product',
            'price' => 12,
            'stock' => 3,
            'is_active' => true,
            'sku' => '5901234123457',
        ]);

        $service = app(ProductBarcodeService::class);
        $this->assertTrue($service->generate($product));
        $product->refresh();

        $this->assertSame('5901234123457', $product->sku);
        $this->assertSame('5901234123457', $product->metadata['barcode_content']);
    }
}
