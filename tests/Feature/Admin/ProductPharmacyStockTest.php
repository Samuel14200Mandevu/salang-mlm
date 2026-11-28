<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductPharmacyStockTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $attrs = []): Product
    {
        return Product::query()->create(array_merge([
            'name' => 'Produit Pharma',
            'slug' => 'produit-pharma-'.Str::lower(Str::random(8)),
            'price' => 2.5,
            'cost' => 1.2,
            'stock' => 20,
            'min_stock' => 10,
            'sku' => 'M-'.Str::upper(Str::random(4)),
            'is_active' => true,
        ], $attrs));
    }

    public function test_pharmacy_status_ok_reorder_out_and_expiring(): void
    {
        $ok = $this->makeProduct(['stock' => 20, 'min_stock' => 10]);
        $reorder = $this->makeProduct(['stock' => 8, 'min_stock' => 10]);
        $out = $this->makeProduct(['stock' => 0, 'min_stock' => 10]);
        $expiring = $this->makeProduct([
            'stock' => 30,
            'min_stock' => 5,
            'expires_at' => now()->addDays(30)->toDateString(),
        ]);
        $expired = $this->makeProduct([
            'stock' => 12,
            'min_stock' => 5,
            'expires_at' => now()->subDay()->toDateString(),
        ]);

        $this->assertSame('ok', $ok->pharmacyStatus());
        $this->assertSame('reorder', $reorder->pharmacyStatus());
        $this->assertSame('out', $out->pharmacyStatus());
        $this->assertSame('expiring', $expiring->pharmacyStatus());
        $this->assertSame('expired', $expired->pharmacyStatus());
    }

    public function test_scopes_below_min_and_expiring(): void
    {
        $this->makeProduct(['stock' => 20, 'min_stock' => 10, 'sku' => 'OK1']);
        $this->makeProduct(['stock' => 4, 'min_stock' => 10, 'sku' => 'LOW1']);
        $this->makeProduct([
            'stock' => 15,
            'min_stock' => 5,
            'sku' => 'EXP1',
            'expires_at' => now()->addDays(15)->toDateString(),
        ]);

        $this->assertSame(1, Product::query()->belowMinStock()->count());
        $this->assertSame(1, Product::query()->expiringSoon()->count());
    }

    public function test_stock_value_uses_cost(): void
    {
        $product = $this->makeProduct(['stock' => 10, 'cost' => 1.5]);

        $this->assertSame(15.0, $product->stockValue());
    }
}
