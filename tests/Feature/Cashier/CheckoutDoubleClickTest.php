<?php

namespace Tests\Feature\Cashier;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * BB1-rapide : garde double-clic sur createCheckoutOrder (CashierController.php:1231+).
 */
class CheckoutDoubleClickTest extends TestCase
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
            'phone' => 'cashier-' . Str::random(8),
        ]);
        $cashier->assignRole('cashier');

        return $cashier;
    }

    private function createSponsor(string $code = 'DBCLKSPON'): User
    {
        return User::factory()->create([
            'is_active' => true,
            'sponsor_id' => $code,
            'user_type' => 'member',
            'phone' => 'sponsor-' . Str::random(8),
        ]);
    }

    private function createProductInStock(float $price = 50.00): Product
    {
        return Product::create([
            'name' => 'Produit double-clic',
            'slug' => 'dbl-' . Str::lower(Str::random(8)),
            'price' => $price,
            'stock' => 100,
            'sku' => 'SKU-' . Str::upper(Str::random(6)),
            'is_active' => true,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cartLineForProduct(Product $product): array
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
            'quantity' => 1,
        ]];
    }

    /**
     * @return array<string, mixed>
     */
    private function checkoutPayload(User $sponsor, string $phone = '+243900000099'): array
    {
        return [
            'name' => 'Client Double Clic',
            'phone' => $phone,
            'sponsor_code' => $sponsor->sponsor_id,
            'commission_amount' => 5,
        ];
    }

    public function test_double_checkout_post_creates_single_order_and_redirects_to_existing(): void
    {
        $bb1WarningLogged = false;

        Event::listen(MessageLogged::class, function (MessageLogged $event) use (&$bb1WarningLogged): void {
            if ($event->level === 'warning' && str_contains($event->message, 'BB1-rapide')) {
                $bb1WarningLogged = true;
            }
        });

        $cashier = $this->createCashierUser();
        $sponsor = $this->createSponsor('DBCLK01');
        $product = $this->createProductInStock();
        $cart = $this->cartLineForProduct($product);
        $payload = $this->checkoutPayload($sponsor);

        $this->actingAs($cashier)->withSession(['pos_cart' => $cart]);

        $first = $this->post(route('cashier.checkout.order'), $payload);
        $first->assertRedirect();

        $client = User::where('phone', $payload['phone'])->first();
        $this->assertNotNull($client);
        $this->assertSame(1, Order::where('user_id', $client->id)->count());

        $existingOrder = Order::where('user_id', $client->id)->first();
        $this->assertNotNull($existingOrder);

        $second = $this->post(route('cashier.checkout.order'), $payload);
        $second->assertRedirect(route('cashier.orders.show', $existingOrder->id));
        $second->assertSessionHas('info');

        $this->assertSame(1, Order::where('user_id', $client->id)->count());

        $this->assertTrue($bb1WarningLogged, 'Log BB1-rapide attendu (CashierController.php garde idempotence)');
    }

    public function test_checkout_allowed_when_previous_order_is_older_than_two_minutes(): void
    {
        $cashier = $this->createCashierUser();
        $sponsor = $this->createSponsor('DBCLK02');
        $product = $this->createProductInStock();
        $cart = $this->cartLineForProduct($product);
        $payload = $this->checkoutPayload($sponsor, '+243900000098');

        $client = User::factory()->create([
            'phone' => $payload['phone'],
            'is_active' => true,
            'user_type' => 'client',
        ]);

        $oldOrder = Order::create([
            'user_id' => $client->id,
            'cashier_id' => $cashier->id,
            'created_by' => $cashier->id,
            'order_number' => 'POS-OLD-' . Str::upper(Str::random(4)),
            'subtotal' => 50,
            'tax' => 0,
            'shipping' => 0,
            'discount' => 0,
            'total' => 50,
            'total_pv' => 0,
            'status' => 'completed',
            'payment_status' => 'completed',
            'payment_method' => 'cash',
            'source' => 'pos',
        ]);
        $oldOrder->created_at = now()->subMinutes(5);
        $oldOrder->updated_at = now()->subMinutes(5);
        $oldOrder->saveQuietly();

        $this->assertSame(1, Order::count());

        $this->actingAs($cashier)
            ->withSession(['pos_cart' => $cart])
            ->post(route('cashier.checkout.order'), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Order::count());
    }

    public function test_checkout_allowed_when_recent_order_is_cancelled(): void
    {
        $cashier = $this->createCashierUser();
        $sponsor = $this->createSponsor('DBCLK03');
        $product = $this->createProductInStock();
        $cart = $this->cartLineForProduct($product);
        $payload = $this->checkoutPayload($sponsor, '+243900000097');

        $client = User::factory()->create([
            'phone' => $payload['phone'],
            'is_active' => true,
            'user_type' => 'client',
        ]);

        Order::create([
            'user_id' => $client->id,
            'cashier_id' => $cashier->id,
            'created_by' => $cashier->id,
            'order_number' => 'POS-CXL-' . Str::upper(Str::random(4)),
            'subtotal' => 50,
            'tax' => 0,
            'shipping' => 0,
            'discount' => 0,
            'total' => 50,
            'total_pv' => 0,
            'status' => 'cancelled',
            'payment_status' => 'cancelled',
            'payment_method' => 'cash',
            'source' => 'pos',
        ]);

        $this->assertSame(1, Order::count());

        $this->actingAs($cashier)
            ->withSession(['pos_cart' => $cart])
            ->post(route('cashier.checkout.order'), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Order::count());
    }
}
