<?php

namespace Tests\Feature\Admin;

use App\Models\Pharmacy\CashEntry;
use App\Models\Pharmacy\Insurer;
use App\Models\Pharmacy\Purchase;
use App\Models\Pharmacy\Sale;
use App\Models\Pharmacy\Supplier;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PharmacyModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    private function makeProduct(array $attrs = []): Product
    {
        return Product::query()->create(array_merge([
            'name' => 'Médicament Test',
            'slug' => 'med-'.Str::lower(Str::random(8)),
            'price' => 10,
            'cost' => 4,
            'stock' => 5,
            'min_stock' => 2,
            'sku' => 'SKU-'.Str::upper(Str::random(4)),
            'is_active' => true,
        ], $attrs));
    }

    public function test_dashboard_is_reachable(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.pharmacy.dashboard'))
            ->assertOk();
    }

    public function test_purchase_receives_stock_and_updates_cost(): void
    {
        $supplier = Supplier::query()->create(['name' => 'Pharma Dist', 'is_active' => true]);
        $product = $this->makeProduct(['stock' => 5, 'cost' => 4]);

        $response = $this->actingAs($this->admin)->post(route('admin.pharmacy.purchases.store'), [
            'purchased_at' => now()->toDateString(),
            'invoice_number' => 'F-100',
            'supplier_id' => $supplier->id,
            'paid_amount' => 50,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'unit_cost' => 5],
            ],
        ]);

        $purchase = Purchase::query()->first();
        $this->assertNotNull($purchase);
        $response->assertRedirect(route('admin.pharmacy.purchases.show', $purchase));

        $product->refresh();
        $this->assertSame(15, (int) $product->stock);
        $this->assertEquals(5.0, (float) $product->cost);
        $this->assertSame('paid', $purchase->status);
        $this->assertTrue(
            StockMovement::query()
                ->where('product_id', $product->id)
                ->where('type', StockMovement::TYPE_ENTRY)
                ->exists()
        );
    }

    public function test_sale_with_insurance_decrements_stock_and_posts_cash(): void
    {
        $insurer = Insurer::query()->create([
            'name' => 'Mutuelle A',
            'coverage_rate' => 0.8,
            'is_active' => true,
        ]);
        $product = $this->makeProduct(['stock' => 10, 'price' => 10, 'cost' => 4]);

        $response = $this->actingAs($this->admin)->post(route('admin.pharmacy.sales.store'), [
            'sold_at' => now()->toDateString(),
            'customer_name' => 'Client Test',
            'payment_method' => 'insurance',
            'insurer_id' => $insurer->id,
            'coverage_rate' => 0.8,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10],
            ],
        ]);

        $sale = Sale::query()->first();
        $this->assertNotNull($sale);
        $response->assertRedirect(route('admin.pharmacy.sales.show', $sale));

        $product->refresh();
        $this->assertSame(8, (int) $product->stock);
        $this->assertEquals(20.0, (float) $sale->total_amount);
        $this->assertEquals(16.0, (float) $sale->insurance_amount);
        $this->assertEquals(4.0, (float) $sale->patient_amount);
        $this->assertEquals(12.0, (float) $sale->profit_amount);

        $this->assertTrue(
            CashEntry::query()
                ->where('category', 'sales_receipt')
                ->where('amount_in', 4)
                ->exists()
        );
    }

    public function test_registers_and_insurers_crud(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.pharmacy.registers.suppliers.store'), [
                'name' => 'Grossiste B',
                'phone' => '099',
            ])
            ->assertRedirect(route('admin.pharmacy.registers.index', ['tab' => 'suppliers']));

        $this->assertDatabaseHas('pharmacy_suppliers', ['name' => 'Grossiste B']);

        $this->actingAs($this->admin)
            ->post(route('admin.pharmacy.insurers.store'), [
                'name' => 'SONAS',
                'coverage_rate' => 0.7,
            ])
            ->assertRedirect();

        $insurer = Insurer::query()->where('name', 'SONAS')->first();
        $this->assertNotNull($insurer);

        $this->actingAs($this->admin)
            ->post(route('admin.pharmacy.registers.patients.store'), [
                'name' => 'Patient X',
                'insurer_id' => $insurer->id,
            ])
            ->assertRedirect(route('admin.pharmacy.registers.index', ['tab' => 'patients']));

        $this->assertDatabaseHas('pharmacy_patients', ['name' => 'Patient X']);
    }
}
