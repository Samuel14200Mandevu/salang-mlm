<?php

namespace Tests\Unit\MLM;

use App\Models\Commission;
use App\Models\CommissionHistory;
use App\Models\User;
use App\Services\MLM\CommissionHistoryPaymentSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionHistoryPaymentSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_history_paid_when_commission_has_source_id(): void
    {
        $user = User::factory()->create();
        $history = CommissionHistory::query()->create([
            'user_id' => $user->id,
            'period' => '2026-04',
            'type' => 'direct',
            'amount' => 20,
            'percentage' => 22,
            'pv_used' => 0,
            'generation' => 0,
            'status' => 'pending',
        ]);

        Commission::query()->create([
            'source_commission_history_id' => $history->id,
            'user_id' => $user->id,
            'period' => '2026-04',
            'type' => 'direct',
            'source' => 'mlm',
            'amount' => 20,
            'percentage' => 22,
            'status' => 'paid',
            'paid_at' => now(),
            'calculation_type' => 'manual',
        ]);

        $updated = app(CommissionHistoryPaymentSync::class)->markPaidForUserPeriod($user->id, '2026-04');

        $this->assertSame(1, $updated);
        $this->assertSame('paid', $history->fresh()->status);
    }
}
