<?php

namespace Tests\Feature\Pv;

use App\Models\PVHistory;
use App\Models\User;
use App\Models\UserPvBalance;
use App\Services\PvPeriodSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class PvPeriodSettlementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_settle_credits_leaf_sponsor_available_pv_to_pv_balance(): void
    {
        $sponsor = User::factory()->create([
            'user_type' => 'member',
            'is_active' => true,
            'pv_balance' => 0,
            'phone' => 'leaf-' . Str::random(8),
        ]);

        UserPvBalance::create([
            'user_id' => $sponsor->id,
            'total_pv' => 120,
            'available_pv' => 120,
            'total_bv' => 0,
            'available_bv' => 0,
        ]);

        $service = app(PvPeriodSettlementService::class);
        $stats = $service->settleClosedPeriod('2026-10', false);

        $this->assertSame(1, $stats['users_settled']);
        $this->assertSame(120, $stats['pv_settled']);

        $sponsor->refresh();
        $this->assertSame(120, (int) $sponsor->pv_balance);

        $balance = UserPvBalance::where('user_id', $sponsor->id)->first();
        $this->assertSame(0, (int) $balance->available_pv);

        $this->assertDatabaseHas('pv_history', [
            'user_id' => $sponsor->id,
            'period' => '2026-10',
            'type' => 'personal',
        ]);
    }

    public function test_settle_credits_remainder_for_sponsor_with_member_downline(): void
    {
        $sponsor = User::factory()->create([
            'user_type' => 'member',
            'is_active' => true,
            'pv_balance' => 50,
            'phone' => 'par-' . Str::random(8),
        ]);

        User::factory()->create([
            'user_type' => 'member',
            'parrain_id' => $sponsor->id,
            'is_active' => true,
            'phone' => 'fil-' . Str::random(8),
        ]);

        UserPvBalance::create([
            'user_id' => $sponsor->id,
            'total_pv' => 200,
            'available_pv' => 30,
            'allocated_pv' => 170,
        ]);

        app(PvPeriodSettlementService::class)->settleClosedPeriod('2026-10', false);

        $sponsor->refresh();
        $this->assertSame(80, (int) $sponsor->pv_balance);

        $history = PVHistory::where('user_id', $sponsor->id)->where('period', '2026-10')->first();
        $this->assertNotNull($history);
        $this->assertStringContainsString('reliquat', (string) $history->notes);
    }
}
