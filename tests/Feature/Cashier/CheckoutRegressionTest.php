<?php

namespace Tests\Feature\Cashier;

use App\Models\Commission;
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

/**
 * Filet de non-régression caisse (AUDIT Sprint 0 #6).
 * Verrouille le comportement actuel de CashierController avant BA12/BA14/BE1.
 *
 * @see app/Http/Controllers/Cashier/CashierController.php
 */
class CheckoutRegressionTest extends TestCase
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

    private function createSponsor(string $code = 'SPONSOR01'): User
    {
        return User::factory()->create([
            'is_active' => true,
            'sponsor_id' => $code,
            'user_type' => 'member',
            'phone' => 'sponsor-' . Str::random(8),
        ]);
    }

    private function createProductInStock(float $price = 50.00, int $stock = 100): Product
    {
        $slug = 'test-product-' . Str::lower(Str::random(8));

        // Colonnes minimales compatibles migration sqlite tests (:memory:).
        // pv/bv sont portés par la ligne de panier session, pas la table products en test.
        return Product::create([
            'name' => 'Produit test caisse',
            'slug' => $slug,
            'price' => $price,
            'stock' => $stock,
            'sku' => 'SKU-' . Str::upper(Str::random(6)),
            'is_active' => true,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cartLineForProduct(Product $product, int $quantity = 1): array
    {
        return [[
            'id' => $product->id,
            'type' => 'product',
            'name' => $product->name,
            'price' => (float) $product->price,
            'source' => 'pos',
            'source_label' => 'POS',
            // PV à 0 : évite PvManagementService/user_pv_balances (tables absentes en sqlite migrate).
            'pv_value' => 0.0,
            'bv_value' => 0.0,
            'quantity' => $quantity,
        ]];
    }

    /**
     * Test 1 — checkout panier valide → commande + commissions.
     * Contrôleur : createCheckoutOrder() CashierController.php:1147-1464
     *
     * Note AUDIT : la spec Sprint 0 mentionnait status/payment_status « pending » ;
     * le code actuel crée la commande en « completed » (:1326-1327) — régression verrouillée tel quel.
     */
    public function test_checkout_with_valid_cart_creates_order_and_commissions(): void
    {
        $cashier = $this->createCashierUser();
        $sponsor = $this->createSponsor('CHKSPON01');
        $product = $this->createProductInStock(75.00);

        $commissionCountBefore = Commission::count();

        $response = $this->actingAs($cashier)
            ->withSession(['pos_cart' => $this->cartLineForProduct($product)])
            ->post(route('cashier.checkout.order'), [
                'name' => 'Client Checkout',
                'phone' => '+243900000001',
                'sponsor_code' => $sponsor->sponsor_id,
                'commission_amount' => 10,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $order = Order::query()->latest('id')->first();
        $this->assertNotNull($order, 'Order must be created (CashierController.php:1313-1343)');

        $this->assertSame('completed', $order->status);
        $this->assertSame('completed', $order->payment_status);
        $this->assertSame('pos', $order->source);
        $this->assertSame($cashier->id, $order->cashier_id);

        $this->assertGreaterThan($commissionCountBefore, Commission::count(), 'Commissions POS créées (CashierController.php:1386-1445)');

        $this->assertDatabaseHas('commissions', [
            'order_id' => $order->id,
            'type' => 'cash_pos',
            'user_id' => $sponsor->id,
        ]);
    }

    /**
     * Test 2 — createCheckoutOrder : user, total, items, statut.
     * Contrôleur : createCheckoutOrder() CashierController.php:1147-1377
     */
    public function test_create_checkout_order_persists_user_total_items_and_status(): void
    {
        $cashier = $this->createCashierUser();
        $sponsor = $this->createSponsor('CHKSPON02');
        $product = $this->createProductInStock(40.00, 50);
        $quantity = 2;
        $cart = $this->cartLineForProduct($product, $quantity);

        $clientPhone = '+243900000002';

        $this->actingAs($cashier)
            ->withSession(['pos_cart' => $cart])
            ->post(route('cashier.checkout.order'), [
                'name' => 'Client Panier',
                'phone' => $clientPhone,
                'sponsor_code' => $sponsor->sponsor_id,
                'commission_amount' => 0,
            ])
            ->assertRedirect();

        $client = User::where('phone', $clientPhone)->first();
        $this->assertNotNull($client);

        $order = Order::where('user_id', $client->id)->latest('id')->first();
        $this->assertNotNull($order);

        $expectedTotal = 40.00 * $quantity;
        $this->assertEqualsWithDelta($expectedTotal, (float) $order->total, 0.01);
        $this->assertSame($client->id, $order->user_id);

        $this->assertSame(1, OrderItem::where('order_id', $order->id)->count());
        $this->assertSame('completed', $order->status, 'CashierController.php:1326');
    }

    /**
     * Test 3 — createOrder POS déclenche commission cash_pos.
     * Contrôleur : createOrder() CashierController.php:191-417 (commission :337-352)
     */
    public function test_create_order_triggers_cash_pos_commission_for_sponsor(): void
    {
        $cashier = $this->createCashierUser();
        $sponsor = $this->createSponsor('POSSPON03');
        $product = $this->createProductInStock(30.00);

        $response = $this->actingAs($cashier)
            ->post(route('cashier.pos.order'), [
                'phone' => '+243900000003',
                'name' => 'Client POS',
                'sponsor_code' => $sponsor->sponsor_id,
                'product_id' => $product->id,
                'quantity' => 1,
                'commission_amount' => 12,
            ]);

        $order = Order::query()->latest('id')->first();
        if ($order === null) {
            $this->fail(sprintf(
                'BUG EXISTANT — aucune commande après POST cashier/pos/order. HTTP %s. Flash error: %s. Validation: %s',
                $response->status(),
                session('error') ?? 'none',
                json_encode(session('errors')?->getMessages() ?? [])
            ));
        }

        $response->assertRedirect();

        $commission = Commission::where('order_id', $order->id)
            ->whereIn('type', ['cash_pos', 'direct'])
            ->first();

        $this->assertNotNull($commission, 'Commission attendue (CashierController.php:337-352)');
        $this->assertSame('cash_pos', $commission->type);
        $this->assertGreaterThan(0, (float) $commission->amount);
        $this->assertEqualsWithDelta(12.0, (float) $commission->amount, 0.01);
        $this->assertSame($sponsor->id, $commission->user_id);
    }
}
