<?php

namespace Tests\Unit;

use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\User;
use App\Services\WalletCommissionBreakdown;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletCommissionBreakdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_bonus_month_options_are_august_and_september(): void
    {
        $service = new WalletCommissionBreakdown;
        $options = $service->bonusMonthOptions(Carbon::parse('2026-11-09'));

        $this->assertSame(['2026-08', '2026-09'], array_keys($options));
        $this->assertStringContainsString('2026', $options['2026-08']);
        $this->assertStringContainsString('2026', $options['2026-09']);
    }

    public function test_paid_by_type_filters_by_mlm_period(): void
    {
        $user = User::factory()->create();
        $august = CommissionPeriod::findOrCreateForValue('2026-08');
        $august->update(['status' => 'paid', 'is_historical' => false, 'is_hidden' => false]);
        $september = CommissionPeriod::findOrCreateForValue('2026-09');
        $september->update(['status' => 'paid', 'is_historical' => false, 'is_hidden' => false]);

        foreach ([['direct', 10.0], ['sponsor', 5.0]] as [$type, $amount]) {
            Commission::query()->create([
                'user_id' => $user->id,
                'commission_period_id' => $august->id,
                'period' => '2026-08',
                'type' => $type,
                'source' => 'mlm',
                'amount' => $amount,
                'percentage' => 0,
                'status' => 'paid',
                'paid_at' => now(),
                'calculation_type' => 'manual',
            ]);
        }

        Commission::query()->create([
            'user_id' => $user->id,
            'commission_period_id' => $september->id,
            'period' => '2026-09',
            'type' => 'direct',
            'source' => 'mlm',
            'amount' => 20,
            'percentage' => 0,
            'status' => 'paid',
            'paid_at' => now(),
            'calculation_type' => 'manual',
        ]);

        $service = new WalletCommissionBreakdown;

        $augustBreakdown = $service->paidByType($user->id, '2026-08');
        $this->assertSame(10.0, $augustBreakdown['direct']['total']);
        $this->assertSame(5.0, $augustBreakdown['sponsor']['total']);
        $this->assertSame(0.0, $augustBreakdown['indirect']['total']);

        $septemberBreakdown = $service->paidByType($user->id, '2026-09');
        $this->assertSame(20.0, $septemberBreakdown['direct']['total']);
        $this->assertSame(0.0, $septemberBreakdown['sponsor']['total']);
    }
}
