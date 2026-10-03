<?php

namespace Tests\Feature\API;

use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiCommissionBandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_index_excludes_hidden_periods(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $visible = CommissionPeriod::findOrCreateForValue('2024-09');
        $visible->update(['is_hidden' => false, 'is_historical' => false, 'status' => 'calculated']);

        $hidden = CommissionPeriod::findOrCreateForValue('2025-06');
        $hidden->update(['is_hidden' => true, 'is_historical' => false, 'status' => 'calculated']);

        Commission::query()->create([
            'user_id' => $user->id,
            'commission_period_id' => $visible->id,
            'period' => $visible->period,
            'type' => 'direct',
            'source' => 'mlm',
            'amount' => 10,
            'percentage' => 0,
            'status' => 'pending',
            'calculation_type' => 'manual',
        ]);

        Commission::query()->create([
            'user_id' => $user->id,
            'commission_period_id' => $hidden->id,
            'period' => $hidden->period,
            'type' => 'direct',
            'source' => 'mlm',
            'amount' => 500,
            'percentage' => 0,
            'status' => 'pending',
            'calculation_type' => 'manual',
        ]);

        $response = $this->withToken($token)->getJson('/api/commissions');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.amount', 10);
    }

    public function test_commission_stats_pending_is_payable_band_only(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $bandB = CommissionPeriod::findOrCreateForValue('2024-09');
        $bandB->update(['is_hidden' => false, 'is_historical' => false]);

        $bandC = CommissionPeriod::findOrCreateForValue('2026-01');
        $bandC->update(['is_hidden' => true]);

        Commission::query()->create([
            'user_id' => $user->id,
            'commission_period_id' => $bandB->id,
            'period' => $bandB->period,
            'type' => 'direct',
            'source' => 'mlm',
            'amount' => 25,
            'percentage' => 0,
            'status' => 'pending',
            'calculation_type' => 'manual',
        ]);

        Commission::query()->create([
            'user_id' => $user->id,
            'commission_period_id' => $bandC->id,
            'period' => $bandC->period,
            'type' => 'direct',
            'source' => 'mlm',
            'amount' => 300,
            'percentage' => 0,
            'status' => 'pending',
            'calculation_type' => 'manual',
        ]);

        $response = $this->withToken($token)->getJson('/api/commissions/stats');

        $response->assertOk();
        $this->assertEquals(25.0, (float) $response->json('stats.pending'));
        $this->assertEquals(25.0, (float) $response->json('stats.bands.payable_pending'));
    }
}
