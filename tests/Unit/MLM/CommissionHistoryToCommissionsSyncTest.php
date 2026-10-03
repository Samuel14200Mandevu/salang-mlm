<?php

namespace Tests\Unit\MLM;

use App\Models\Commission;
use App\Models\CommissionHistory;
use App\Models\CommissionPeriod;
use App\Models\User;
use App\Services\MLM\CommissionHistoryToCommissionsSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionHistoryToCommissionsSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_is_idempotent_by_source_history_id(): void
    {
        $user = User::factory()->create();
        $history = CommissionHistory::query()->create([
            'user_id' => $user->id,
            'from_user_id' => $user->id,
            'period' => '2026-04',
            'type' => 'direct',
            'amount' => 42.50,
            'percentage' => 22,
            'pv_used' => 100,
            'generation' => 0,
            'status' => 'pending',
            'description' => 'Test direct',
        ]);

        $sync = app(CommissionHistoryToCommissionsSync::class);

        $sync->ensurePeriod('2026-04');
        $first = $sync->syncHistoryRow($history, null, false);
        $second = $sync->syncHistoryRow($history->fresh(), null, false);

        $this->assertSame(1, $first['created']);
        $this->assertSame(0, $second['created']);
        $this->assertSame(1, $second['skipped']);
        $this->assertSame(1, Commission::query()->count());
        $this->assertSame($history->id, Commission::query()->value('source_commission_history_id'));
    }

    public function test_ensure_period_sets_calculated_status(): void
    {
        CommissionHistory::query()->create([
            'user_id' => User::factory()->create()->id,
            'period' => '2026-05',
            'type' => 'indirect',
            'amount' => 10,
            'percentage' => 5,
            'pv_used' => 0,
            'generation' => 1,
            'status' => 'pending',
        ]);

        app(CommissionHistoryToCommissionsSync::class)->ensurePeriod('2026-05');

        $period = CommissionPeriod::query()->where('period', '2026-05')->first();
        $this->assertNotNull($period);
        $this->assertSame('calculated', $period->status);
        $this->assertEqualsWithDelta(10.0, (float) $period->total_commissions, 0.01);
    }
}
