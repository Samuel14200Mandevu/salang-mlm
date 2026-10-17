<?php

namespace Tests\Feature\MLM;

use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PVHistory;
use App\Models\User;
use App\Services\MLM\CommissionDistributor;
use App\Services\MLM\PeriodCommissionCalculator;
use App\Support\MlmPeriod;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommissionIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function currentPeriod(): CommissionPeriod
    {
        return CommissionPeriod::firstOrCreate(
            ['period' => MlmPeriod::current()],
            [
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'status' => 'pending',
            ]
        );
    }

    private function createProduct(float $price = 40.0): Product
    {
        return Product::create([
            'name' => 'Produit BA12 ' . Str::random(4),
            'slug' => 'ba12-' . Str::lower(Str::random(8)),
            'price' => $price,
            'stock' => 50,
            'sku' => 'BA12-' . Str::upper(Str::random(5)),
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<int, Product>  $products
     */
    private function createPosOrder(User $buyer, array $products, float $commissionAmount = 12.0): Order
    {
        return Order::withoutEvents(function () use ($buyer, $products, $commissionAmount) {
            $order = Order::create([
                'user_id' => $buyer->id,
                'order_number' => 'POS-BA12-' . Str::upper(Str::random(6)),
                'subtotal' => collect($products)->sum(fn (Product $p) => $p->price),
                'total' => collect($products)->sum(fn (Product $p) => $p->price),
                'status' => 'completed',
                'payment_status' => 'completed',
                'source' => 'pos',
                'metadata' => ['commission_amount' => $commissionAmount],
                'paid_at' => now(),
            ]);

            foreach ($products as $product) {
                OrderItem::withoutEvents(function () use ($order, $product) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'quantity' => 1,
                        'price' => $product->price,
                        'total' => $product->price,
                        'pv_value' => (int) ($product->pv_value ?? 0),
                        'bv_value' => 0,
                    ]);
                });
            }

            return $order->fresh(['items']);
        });
    }

    private function posFixtures(): array
    {
        $this->currentPeriod();

        $sponsor = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
        ]);

        $buyer = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $sponsor->id,
        ]);

        return [$sponsor, $buyer];
    }

    public function test_double_distribute_commissions_for_order_creates_single_commission_row_set(): void
    {
        [, $buyer] = $this->posFixtures();
        $order = $this->createPosOrder($buyer, [$this->createProduct()]);

        $distributor = app(CommissionDistributor::class);
        $distributor->distributeCommissionsForOrder($order);
        $distributor->distributeCommissionsForOrder($order->fresh(['items']));

        $this->assertSame(1, Commission::where('order_id', $order->id)->count());
    }

    public function test_multi_line_pos_cart_creates_only_one_cash_pos_commission(): void
    {
        [, $buyer] = $this->posFixtures();
        $order = $this->createPosOrder($buyer, [
            $this->createProduct(30),
            $this->createProduct(35),
            $this->createProduct(25),
        ]);

        app(CommissionDistributor::class)->distributeCommissionsForOrder($order);

        $this->assertSame(
            1,
            Commission::where('order_id', $order->id)->where('type', 'cash_pos')->count()
        );
    }

    public function test_period_calc_keeps_single_cash_pos_and_still_creates_period_bonuses(): void
    {
        $this->currentPeriod();
        [, $buyer] = $this->posFixtures();
        $period = CommissionPeriod::where('period', MlmPeriod::current())->firstOrFail();

        $buyer->forceFill([
            'rank_level' => 3,
            'rank' => 'Qualification',
            'monthly_pv' => 0,
            'team_pv' => 0,
        ])->saveQuietly();

        $order = $this->createPosOrder($buyer, [$this->createProduct()]);

        app(CommissionDistributor::class)->distributeCommissionsForOrder($order);

        $extraPv = PVHistory::create([
            'user_id' => $buyer->id,
            'amount' => 200,
            'date' => now()->toDateString(),
            'period' => $period->period,
            'type' => 'personal',
            'notes' => 'PV hors commande POS (fin de période)',
            'created_by' => $buyer->id,
        ]);
        $extraPv->order_id = null;
        $extraPv->status = 'posted';
        $extraPv->saveQuietly();

        app(PeriodCommissionCalculator::class)->calculateForPeriod($period, $buyer->id);

        $this->assertSame(
            1,
            Commission::where('order_id', $order->id)->where('type', 'cash_pos')->count()
        );
        $this->assertGreaterThan(
            0,
            Commission::where('user_id', $buyer->id)->whereIn('type', ['direct', 'indirect', 'leadership'])->count()
        );
    }

    public function test_unique_order_id_and_type_prevents_duplicate_commission_rows(): void
    {
        $period = $this->currentPeriod();
        $user = User::factory()->create(['is_active' => true]);

        $order = Order::withoutEvents(fn () => Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-UNIQ-' . Str::random(6),
            'subtotal' => 10,
            'total' => 10,
            'status' => 'completed',
            'payment_status' => 'completed',
            'source' => 'pos',
            'paid_at' => now(),
        ]));

        $base = [
            'user_id' => $user->id,
            'commission_period_id' => $period->id,
            'period' => $period->period,
            'type' => 'cash_pos',
            'source' => 'pos',
            'amount' => 10,
            'percentage' => 0,
            'order_id' => $order->id,
            'status' => 'paid',
            'paid_at' => now(),
        ];

        Commission::create($base);

        $this->expectException(QueryException::class);
        Commission::create($base);
    }
}
