<?php

namespace Tests\Feature\MLM;

use App\Models\User;
use App\Models\Rank;
use App\Models\Commission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected MonthlyCommissionService $commissionService;
    protected AdvancedRankCalculator $rankCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commissionService = app(MonthlyCommissionService::class);
        $this->rankCalculator = app(AdvancedRankCalculator::class);
        $this->createRanks();
    }

    private function createRanks(): void
    {
        $ranks = [
            ['level' => 1, 'name' => 'Distributeur', 'slug' => 'distributor', 'min_pv' => 0, 'min_bv' => 0, 'bonus_percentage' => 6, 'is_active' => true],
            ['level' => 2, 'name' => 'Qualification', 'slug' => 'qualification', 'min_pv' => 100, 'min_bv' => 100, 'bonus_percentage' => 6, 'is_active' => true],
            ['level' => 3, 'name' => 'Cumul Directeur', 'slug' => 'assistant-manager', 'min_pv' => 200, 'min_bv' => 200, 'bonus_percentage' => 22, 'is_active' => true],
            ['level' => 4, 'name' => 'Directeur', 'slug' => 'manager', 'min_pv' => 1000, 'min_bv' => 1000, 'bonus_percentage' => 26, 'is_active' => true],
            ['level' => 5, 'name' => 'Manager Senior', 'slug' => 'senior-manager', 'min_pv' => 3800, 'min_bv' => 3800, 'bonus_percentage' => 30, 'is_active' => true],
            ['level' => 6, 'name' => 'Directeur Envolée', 'slug' => 'soaring-manager', 'min_pv' => 16000, 'min_bv' => 16000, 'bonus_percentage' => 34, 'is_active' => true],
            ['level' => 7, 'name' => 'Saphire Manager', 'slug' => 'sapphire-manager', 'min_pv' => 73000, 'min_bv' => 73000, 'bonus_percentage' => 40, 'is_active' => true],
            ['level' => 8, 'name' => 'Diamant Bleu', 'slug' => 'blue-diamond', 'min_pv' => 280000, 'min_bv' => 280000, 'bonus_percentage' => 43, 'is_active' => true],
            ['level' => 9, 'name' => 'Perle Diamant', 'slug' => 'diamond', 'min_pv' => 400000, 'min_bv' => 400000, 'bonus_percentage' => 45, 'is_active' => true],
        ];

        foreach ($ranks as $rankData) {
            Rank::create($rankData);
        }
    }

    private function createProduct(): Product
    {
        return Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100,
            'pv_value' => 10,
            'bv_value' => 10,
            'stock' => 100,
            'is_active' => true,
        ]);
    }

    public function test_direct_commission_is_calculated(): void
    {
        $sponsor = User::factory()->create(['pv_balance' => 3800, 'bv_balance' => 3800]);
        $child = User::factory()->create(['parrain_id' => $sponsor->id, 'pv_balance' => 1000, 'bv_balance' => 1000]);

        // Assigner les grades
        $sponsorRank = $this->rankCalculator->calculateAdvancedRank($sponsor);
        $childRank = $this->rankCalculator->calculateAdvancedRank($child);

        $sponsor->rank_id = $sponsorRank->id;
        $sponsor->rank = $sponsorRank->name;
        $sponsor->save();

        $child->rank_id = $childRank->id;
        $child->rank = $childRank->name;
        $child->save();

        // Créer un produit et une commande
        $product = $this->createProduct();
        $order = Order::create([
            'user_id' => $child->id,
            'order_number' => 'ORD-TEST-001',
            'subtotal' => 100,
            'total' => 100,
            'status' => 'completed',
            'payment_status' => 'completed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 1,
            'price' => 100,
            'total' => 100,
            'pv_value' => 10,
            'bv_value' => 10,
        ]);

        // Mettre à jour le PV mensuel
        $child->monthly_pv = 10;
        $child->monthly_bv = 10;
        $child->save();

        // Créer une période
        $period = CommissionPeriod::create([
            'period' => '2024-01',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'status' => 'pending',
        ]);

        // Calculer les commissions
        $result = $this->commissionService->calculateMonthlyCommissions($period->id);

        $this->assertTrue($result);

        $commission = Commission::where('user_id', $sponsor->id)
            ->where('type', 'direct')
            ->first();

        $this->assertNotNull($commission);
        $this->assertGreaterThan(0, $commission->amount);
    }

    public function test_consumer_bonus_is_calculated(): void
    {
        $user = User::factory()->create(['pv_balance' => 200, 'bv_balance' => 200]);

        $user->monthly_pv = 100;
        $user->monthly_bv = 100;
        $user->save();

        $rank = $this->rankCalculator->calculateAdvancedRank($user);
        $user->rank_id = $rank->id;
        $user->rank = $rank->name;
        $user->save();

        $period = CommissionPeriod::create([
            'period' => '2024-01',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'status' => 'pending',
        ]);

        $result = $this->commissionService->calculateMonthlyCommissions($period->id);

        $this->assertTrue($result);

        $commission = Commission::where('user_id', $user->id)
            ->where('type', 'consumer')
            ->first();

        $this->assertNotNull($commission);
        $this->assertEquals(6, $commission->percentage);
    }
}