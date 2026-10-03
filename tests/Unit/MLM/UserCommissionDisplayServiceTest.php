<?php

namespace Tests\Unit\MLM;

use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\User;
use App\Services\MLM\UserCommissionDisplayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCommissionDisplayServiceTest extends TestCase
{
    use RefreshDatabase;

    private function period(string $value, array $flags): CommissionPeriod
    {
        $period = CommissionPeriod::findOrCreateForValue($value);
        $period->update(array_merge([
            'status' => 'calculated',
            'is_historical' => false,
            'is_hidden' => false,
        ], $flags));

        return $period->fresh();
    }

    private function commission(User $user, CommissionPeriod $period, float $amount, string $status): Commission
    {
        return Commission::query()->create([
            'user_id' => $user->id,
            'commission_period_id' => $period->id,
            'period' => $period->period,
            'type' => 'direct',
            'source' => 'mlm',
            'amount' => $amount,
            'percentage' => 0,
            'status' => $status,
            'calculation_type' => 'manual',
        ]);
    }

    public function test_summarize_splits_bands_for_member(): void
    {
        $user = User::factory()->create();
        $service = app(UserCommissionDisplayService::class);

        $bandA = $this->period('2024-01', ['is_historical' => true, 'status' => 'paid']);
        $bandB = $this->period('2024-09', ['is_historical' => false, 'is_hidden' => false]);
        $bandC = $this->period('2025-01', ['is_hidden' => true]);

        $this->commission($user, $bandA, 100, 'paid');
        $this->commission($user, $bandB, 40, 'pending');
        $this->commission($user, $bandC, 999, 'pending');

        $summary = $service->summarize($user->id);

        $this->assertSame(100.0, $summary['paid_offline_historical']);
        $this->assertSame(0.0, $summary['paid_system']);
        $this->assertSame(100.0, $summary['paid_total_visible']);
        $this->assertSame(40.0, $summary['pending_payable']);
        $this->assertSame(140.0, $summary['total_visible']);
    }
}
