<?php

namespace Tests\Feature\Cashier;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CashierAuditFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Queue::fake();
    }

    private function createCashierUser(): User
    {
        Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        $cashier = User::factory()->create([
            'is_active' => true,
            'phone' => 'cashier-'.Str::random(8),
        ]);
        $cashier->assignRole('cashier');

        return $cashier;
    }

    private function createSponsor(string $code): User
    {
        return User::factory()->create([
            'is_active' => true,
            'sponsor_id' => $code,
            'user_type' => 'member',
            'phone' => 'sponsor-'.Str::random(8),
        ]);
    }

    private function createProduct(int $stock = 5, float $price = 40.00): Product
    {
        return Product::create([
            'name' => 'Produit audit',
            'slug' => 'audit-'.Str::lower(Str::random(8)),
            'price' => $price,
            'stock' => $stock,
            'sku' => 'SKU-'.Str::upper(Str::random(6)),
            'is_active' => true,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cartFor(Product $product, int $qty = 1): array
    {
        return [[
            'id' => $product->id,
            'type' => 'product',
            'name' => $product->name,
            'price' => (float) $product->price,
            'source' => 'pos',
            'source_label' => 'POS',
            'pv_value' => 0.0,
            'bv_value' => 0.0,
            'quantity' => $qty,
        ]];
    }

    public function test_checkout_refuses_member_phone(): void
    {
        $cashier = $this->createCashierUser();
        $sponsor = $this->createSponsor('AUDMEM01');
        $product = $this->createProduct(10);

        $memberPhone = '+243911111111';
        User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'sponsor_id' => 'MEMPHONE1',
            'phone' => $memberPhone,
            'name' => 'Membre Telephone',
        ]);

        $response = $this->actingAs($cashier)
            ->withSession(['pos_cart' => $this->cartFor($product)])
            ->post(route('cashier.checkout.order'), [
                'name' => 'Client X',
                'phone' => $memberPhone,
                'sponsor_code' => $sponsor->sponsor_id,
                'commission_amount' => 0,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame(0, Order::count());
        $this->assertSame(10, (int) $product->fresh()->stock);
    }

    public function test_checkout_refuses_insufficient_stock(): void
    {
        $cashier = $this->createCashierUser();
        $sponsor = $this->createSponsor('AUDSTK01');
        $product = $this->createProduct(1, 25.00);

        $response = $this->actingAs($cashier)
            ->withSession(['pos_cart' => $this->cartFor($product, 3)])
            ->post(route('cashier.checkout.order'), [
                'name' => 'Client Stock',
                'phone' => '+243922222222',
                'sponsor_code' => $sponsor->sponsor_id,
                'commission_amount' => 0,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame(0, Order::count());
        $this->assertSame(1, (int) $product->fresh()->stock);
    }

    public function test_create_order_decrements_stock(): void
    {
        $cashier = $this->createCashierUser();
        $sponsor = $this->createSponsor('AUDSNG01');
        $product = $this->createProduct(8, 30.00);

        $response = $this->actingAs($cashier)
            ->post(route('cashier.pos.order'), [
                'phone' => '+243933333333',
                'name' => 'Client Single',
                'sponsor_code' => $sponsor->sponsor_id,
                'product_id' => $product->id,
                'quantity' => 2,
                'commission_amount' => 0,
            ]);

        $response->assertRedirect();
        $this->assertSame(1, Order::count());
        $this->assertSame(6, (int) $product->fresh()->stock);
    }

    public function test_create_customer_refuses_member_phone(): void
    {
        $cashier = $this->createCashierUser();
        $memberPhone = '+243944444444';

        User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'sponsor_id' => 'MEMCUST1',
            'phone' => $memberPhone,
            'name' => 'Membre Client',
        ]);

        $response = $this->actingAs($cashier)
            ->postJson(route('cashier.customers.store'), [
                'name' => 'Nouveau Client',
                'email' => 'nouveau.client@example.com',
                'phone' => $memberPhone,
            ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', [
            'email' => 'nouveau.client@example.com',
            'user_type' => 'client',
        ]);
    }

    public function test_request_cancellation_stores_pending_metadata(): void
    {
        $cashier = $this->createCashierUser();
        $client = User::factory()->create([
            'user_type' => 'client',
            'is_active' => true,
            'phone' => '+243955555555',
        ]);

        $order = Order::create([
            'user_id' => $client->id,
            'cashier_id' => $cashier->id,
            'created_by' => $cashier->id,
            'order_number' => 'POS-CXL-'.Str::upper(Str::random(4)),
            'subtotal' => 40,
            'tax' => 0,
            'shipping' => 0,
            'discount' => 0,
            'total' => 40,
            'total_pv' => 0,
            'status' => 'completed',
            'payment_status' => 'completed',
            'payment_method' => 'cash',
            'source' => 'pos',
            'metadata' => [],
        ]);

        $response = $this->actingAs($cashier)
            ->postJson(route('cashier.orders.request-cancellation', $order->id), [
                'reason' => 'Erreur de saisie caissier test',
            ]);

        $response->assertOk()->assertJson(['success' => true]);

        $order->refresh();
        $this->assertSame('pending', data_get($order->metadata, 'cancellation_request.status'));
        $this->assertSame('Erreur de saisie caissier test', data_get($order->metadata, 'cancellation_request.reason'));
    }

    public function test_checkout_rejects_commission_between_one_and_three(): void
    {
        $cashier = $this->createCashierUser();
        $sponsor = $this->createSponsor('AUDCOM01');
        $product = $this->createProduct(5);

        $response = $this->actingAs($cashier)
            ->withSession(['pos_cart' => $this->cartFor($product)])
            ->post(route('cashier.checkout.order'), [
                'name' => 'Client Comm',
                'phone' => '+243966666666',
                'sponsor_code' => $sponsor->sponsor_id,
                'commission_amount' => 2,
            ]);

        $response->assertSessionHasErrors('commission_amount');
        $this->assertSame(0, Order::count());
    }

    public function test_expense_search_members_excludes_clients(): void
    {
        $cashier = $this->createCashierUser();

        User::factory()->create([
            'name' => 'Alpha Membre',
            'user_type' => 'member',
            'is_active' => true,
            'sponsor_id' => 'ALPHAMEM',
            'phone' => '+243977777771',
        ]);

        User::factory()->create([
            'name' => 'Alpha Client',
            'user_type' => 'client',
            'is_active' => true,
            'sponsor_id' => 'CLTALPHA',
            'phone' => '+243977777772',
        ]);

        $response = $this->actingAs($cashier)
            ->getJson(route('cashier.expenses.search-members', ['q' => 'Alpha']));

        $response->assertOk();
        $names = collect($response->json())->pluck('name');
        $this->assertTrue($names->contains('Alpha Membre'));
        $this->assertFalse($names->contains('Alpha Client'));
    }
}
