<?php

namespace Tests\Unit\MLM;

use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\User;
use App\Models\Wallet;
use App\Services\MLM\MonthlyCommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneratePaymentsGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_refuses_hidden_period_without_wallet_credit(): void
    {
        $user = User::factory()->create();
        $period = CommissionPeriod::findOrCreateForValue('2025-03');
        $period->update([
            'status' => 'calculated',
            'is_hidden' => true,
            'is_historical' => false,
        ]);

        Commission::query()->create([
            'user_id' => $user->id,
            'commission_period_id' => $period->id,
            'period' => $period->period,
            'type' => 'direct',
            'source' => 'mlm',
            'amount' => 50,
            'percentage' => 0,
            'status' => 'pending',
            'calculation_type' => 'manual',
        ]);

        $ok = app(MonthlyCommissionService::class)->generatePayments($period->id);

        $this->assertFalse($ok);
        $this->assertSame('pending', Commission::query()->first()->status);
        $this->assertSame(0, Wallet::query()->count());
        $this->assertSame('calculated', $period->fresh()->status);
    }

    public function test_refuses_historical_period_even_if_calculated(): void
    {
        $user = User::factory()->create();
        $period = CommissionPeriod::findOrCreateForValue('2024-03');
        $period->update([
            'status' => 'calculated',
            'is_historical' => true,
            'is_hidden' => false,
        ]);

        Commission::query()->create([
            'user_id' => $user->id,
            'commission_period_id' => $period->id,
            'period' => $period->period,
            'type' => 'direct',
            'source' => 'mlm',
            'amount' => 25,
            'percentage' => 0,
            'status' => 'pending',
            'calculation_type' => 'manual',
        ]);

        $ok = app(MonthlyCommissionService::class)->generatePayments($period->id);

        $this->assertFalse($ok);
        $this->assertSame('pending', Commission::query()->first()->status);
        $this->assertSame(0, Wallet::query()->count());
    }
}
