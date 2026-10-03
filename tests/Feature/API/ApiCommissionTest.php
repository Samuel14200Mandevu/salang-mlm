<?php

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\Commission;
use App\Models\CommissionPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiCommissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_user_commissions(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $period = CommissionPeriod::findOrCreateForValue('2024-09');
        $period->update(['is_hidden' => false, 'is_historical' => false]);

        Commission::factory()->create([
            'user_id' => $user->id,
            'commission_period_id' => $period->id,
            'period' => $period->period,
            'amount' => 100,
            'status' => 'paid',
            'type' => 'direct',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/commissions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'amount',
                    'type',
                    'status',
                ]
            ]
        ]);
    }

    public function test_api_returns_commission_stats(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $period = CommissionPeriod::findOrCreateForValue('2024-09');
        $period->update(['is_hidden' => false, 'is_historical' => false]);

        Commission::factory()->create([
            'user_id' => $user->id,
            'commission_period_id' => $period->id,
            'period' => $period->period,
            'amount' => 100,
            'status' => 'paid',
            'type' => 'direct',
        ]);

        Commission::factory()->create([
            'user_id' => $user->id,
            'commission_period_id' => $period->id,
            'period' => $period->period,
            'amount' => 50,
            'status' => 'pending',
            'type' => 'indirect',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/commissions/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'stats' => [
                'total',
                'pending',
                'paid',
                'total_count',
                'pending_count',
                'paid_count',
            ]
        ]);
    }

    public function test_api_returns_wallet_balance(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/wallet');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'balance',
            'pending_balance',
        ]);
    }

    public function test_unauthorized_api_request_returns_401(): void
    {
        $response = $this->get('/api/commissions');

        $response->assertStatus(401);
    }
}