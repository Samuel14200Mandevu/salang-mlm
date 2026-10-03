<?php

namespace Tests\Feature\MLM;

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\PVHistory;
use App\Models\CommissionPeriod;
use App\Services\MLM\CommissionDistributor;
use App\Support\MlmPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CommissionCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function currentPeriod(): CommissionPeriod
    {
        return CommissionPeriod::create([
            'period' => MlmPeriod::current(),
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'status' => 'pending',
        ]);
    }

    public function test_package_purchase_records_pv_history_for_current_mlm_period(): void
    {
        $period = $this->currentPeriod();

        $sponsor = User::factory()->create(['is_active' => true]);
        $buyer = User::factory()->create([
            'parrain_id' => $sponsor->id,
            'user_type' => 'member',
            'is_active' => true,
            'monthly_pv' => 0,
        ]);

        $package = Package::where('slug', 'bronze')->firstOrFail();

        $order = Order::withoutEvents(function () use ($buyer, $package) {
            return Order::create([
                'user_id' => $buyer->id,
                'order_number' => 'ORD-' . uniqid(),
                'subtotal' => $package->price,
                'total' => $package->price,
                'status' => 'completed',
                'payment_status' => 'completed',
                'source' => 'web',
                'paid_at' => now(),
            ]);
        });

        $distributor = app(CommissionDistributor::class);
        $distributor->distributeCommissions($buyer, $package, $order->id, $period);

        $history = PVHistory::where('user_id', $buyer->id)->first();

        $this->assertNotNull($history);
        $this->assertSame(MlmPeriod::current(), $history->period);
        $this->assertGreaterThan(0, (float) $buyer->fresh()->monthly_pv);
    }

    public function test_commissions_only_mode_does_not_increment_monthly_pv(): void
    {
        $period = $this->currentPeriod();

        $buyer = User::factory()->create([
            'user_type' => 'member',
            'is_active' => true,
        ]);
        $buyer->forceFill(['monthly_pv' => 50, 'pv_balance' => 50])->saveQuietly();

        $package = Package::where('slug', 'bronze')->firstOrFail();

        $order = Order::withoutEvents(function () use ($buyer, $package) {
            return Order::create([
                'user_id' => $buyer->id,
                'order_number' => 'ORD-' . uniqid(),
                'subtotal' => $package->price,
                'total' => $package->price,
                'status' => 'completed',
                'payment_status' => 'completed',
                'source' => 'web',
                'paid_at' => now(),
            ]);
        });

        $distributor = app(CommissionDistributor::class);
        $distributor->distributeCommissions($buyer, $package, $order->id, $period, true);

        $this->assertSame(50.0, (float) $buyer->fresh()->monthly_pv);
        $this->assertDatabaseCount('pv_history', 0);
    }
}
