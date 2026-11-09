<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\ProductStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class ProductStockServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock = 10): Product
    {
        // Colonnes minimales compatibles migration sqlite tests (:memory:).
        return Product::query()->create([
            'name' => 'Produit Test',
            'slug' => 'produit-test-'.Str::lower(Str::random(8)),
            'price' => 25,
            'stock' => $stock,
            'sku' => 'SKU-'.Str::upper(Str::random(6)),
            'is_active' => true,
        ]);
    }

    public function test_receive_increases_stock_and_logs_entry(): void
    {
        $product = $this->makeProduct(5);
        $admin = User::factory()->create();

        $movement = app(ProductStockService::class)->receive($product, 3, 'Réception', $admin);

        $this->assertSame(8, (int) $product->fresh()->stock);
        $this->assertSame(StockMovement::TYPE_ENTRY, $movement->type);
        $this->assertSame(3, $movement->quantity_delta);
        $this->assertSame(5, $movement->quantity_before);
        $this->assertSame(8, $movement->quantity_after);
    }

    public function test_remove_refuses_when_insufficient(): void
    {
        $product = $this->makeProduct(2);

        $this->expectException(RuntimeException::class);
        app(ProductStockService::class)->remove($product, 5, 'Casse');
    }

    public function test_consume_for_sale_and_restore_on_cancel(): void
    {
        $product = $this->makeProduct(10);
        $user = User::factory()->create();

        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'POS-TEST-'.Str::upper(Str::random(6)),
            'subtotal' => 50,
            'total' => 50,
            'status' => 'completed',
            'payment_status' => 'completed',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 4,
            'price' => 12.5,
            'total' => 50,
        ]);

        $service = app(ProductStockService::class);
        $service->consumeForSale($product->id, 4, $order, $product->name);

        $this->assertSame(6, (int) $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_SALE,
            'quantity_delta' => -4,
            'reference_id' => $order->id,
        ]);

        $service->restoreForCancelledOrder($order->fresh());

        $this->assertSame(10, (int) $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_SALE_CANCEL,
            'quantity_delta' => 4,
        ]);
    }

    public function test_adjust_to_sets_absolute_stock(): void
    {
        $product = $this->makeProduct(7);
        $admin = User::factory()->create();

        app(ProductStockService::class)->adjustTo($product, 12, 'Inventaire', $admin);

        $this->assertSame(12, (int) $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovement::TYPE_ADJUSTMENT,
            'quantity_delta' => 5,
            'quantity_after' => 12,
        ]);
    }
}
