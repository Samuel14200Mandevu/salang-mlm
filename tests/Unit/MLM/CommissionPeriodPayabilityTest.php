<?php

namespace Tests\Unit\MLM;

use App\Models\CommissionPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionPeriodPayabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_payable_via_system_when_calculated_and_not_flags(): void
    {
        $period = CommissionPeriod::findOrCreateForValue('2024-09');
        $period->update([
            'status' => 'calculated',
            'is_historical' => false,
            'is_hidden' => false,
        ]);

        $this->assertTrue($period->fresh()->isPayableViaSystem());
        $this->assertNull($period->fresh()->paymentBlockReason());
    }

    public function test_blocks_historical_offline(): void
    {
        $period = CommissionPeriod::findOrCreateForValue('2024-01');
        $period->update([
            'status' => 'calculated',
            'is_historical' => true,
            'is_hidden' => false,
        ]);

        $this->assertFalse($period->fresh()->isPayableViaSystem());
        $this->assertSame('historical_offline', $period->fresh()->paymentBlockReason());
    }

    public function test_blocks_hidden_period(): void
    {
        $period = CommissionPeriod::findOrCreateForValue('2025-01');
        $period->update([
            'status' => 'calculated',
            'is_historical' => false,
            'is_hidden' => true,
        ]);

        $this->assertFalse($period->fresh()->isPayableViaSystem());
        $this->assertSame('hidden_period', $period->fresh()->paymentBlockReason());
    }

    public function test_blocks_non_calculated_status(): void
    {
        $period = CommissionPeriod::findOrCreateForValue('2024-08');
        $period->update([
            'status' => 'paid',
            'is_historical' => true,
            'is_hidden' => false,
        ]);

        $this->assertFalse($period->fresh()->isPayableViaSystem());
        $this->assertSame('historical_offline', $period->fresh()->paymentBlockReason());
    }

    public function test_payable_via_system_scope(): void
    {
        CommissionPeriod::findOrCreateForValue('2024-09')->update([
            'status' => 'calculated',
            'is_historical' => false,
            'is_hidden' => false,
        ]);
        CommissionPeriod::findOrCreateForValue('2025-06')->update([
            'status' => 'calculated',
            'is_historical' => false,
            'is_hidden' => true,
        ]);

        $payable = CommissionPeriod::query()->payableViaSystem()->pluck('period')->all();

        $this->assertContains('2024-09', $payable);
        $this->assertNotContains('2025-06', $payable);
    }
}
