<?php

namespace Tests\Feature\MLM;

use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\PVHistory;
use App\Models\User;
use App\Services\MLM\PeriodCommissionCalculator;
use App\Support\MlmPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Régression : la compensation « déjà payé » doit être par (type + from_user),
 * pas par type seul — sinon un résidu est attribué au mauvais downline.
 */
class PeriodCommissionPaidNettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_recalc_creates_missing_source_without_residual_on_other_downline(): void
    {
        $period = CommissionPeriod::firstOrCreate(
            ['period' => MlmPeriod::current()],
            [
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'status' => 'pending',
            ]
        );

        $leader = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'rank_level' => 6,
            'rank' => 'Directeur Envolée',
            'team_pv' => 5000,
            'sponsor_id' => '511621',
        ]);

        // Gen 1 sous le leader
        $mid = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $leader->id,
            'rank_level' => 4,
            'rank' => 'Supervisor',
            'sponsor_id' => '514674',
        ]);

        // Source manquante au 1er paiement (comme 516450)
        $missing = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $mid->id,
            'rank_level' => 5,
            'rank' => 'Manager Senior',
            'sponsor_id' => '516450',
        ]);

        // Autre downline déjà payé (comme 516992)
        $other = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $mid->id,
            'rank_level' => 3,
            'rank' => 'Qualification',
            'sponsor_id' => '516992',
        ]);

        foreach ([[$leader, 60], [$missing, 50], [$other, 200]] as [$user, $pv]) {
            PVHistory::create([
                'user_id' => $user->id,
                'amount' => $pv,
                'date' => now()->toDateString(),
                'period' => $period->period,
                'type' => 'personal',
                'status' => 'posted',
                'notes' => 'test netting',
                'created_by' => $leader->id,
            ]);
        }

        $calculator = app(PeriodCommissionCalculator::class);
        $calculator->calculateForPeriod($period, $leader->id);

        $indirectMissing = Commission::query()
            ->where('user_id', $leader->id)
            ->where('from_user_id', $missing->id)
            ->where('type', 'indirect')
            ->where('period', $period->period)
            ->first();
        $indirectOther = Commission::query()
            ->where('user_id', $leader->id)
            ->where('from_user_id', $other->id)
            ->where('type', 'indirect')
            ->where('period', $period->period)
            ->first();

        $this->assertNotNull($indirectMissing);
        $this->assertNotNull($indirectOther);
        $expectedMissing = (float) $indirectMissing->amount;
        $expectedOther = (float) $indirectOther->amount;

        // Simule l’ancien bug : on a payé l’autre + un faux résidu, pas la vraie source.
        Commission::query()
            ->where('user_id', $leader->id)
            ->where('commission_period_id', $period->id)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->delete();

        Commission::create([
            'user_id' => $leader->id,
            'from_user_id' => $other->id,
            'commission_period_id' => $period->id,
            'period' => $period->period,
            'type' => 'indirect',
            'source' => 'mlm',
            'amount' => $expectedOther,
            'percentage' => 12,
            'pv_used' => 200,
            'generation' => 2,
            'calculation_type' => 'automatic',
            'status' => 'paid',
            'paid_at' => now(),
            'description' => 'Payé other',
        ]);
        Commission::create([
            'user_id' => $leader->id,
            'from_user_id' => $other->id,
            'commission_period_id' => $period->id,
            'period' => $period->period,
            'type' => 'indirect',
            'source' => 'mlm',
            'amount' => $expectedMissing,
            'percentage' => 12,
            'pv_used' => 200,
            'generation' => 2,
            'calculation_type' => 'automatic',
            'status' => 'paid',
            'paid_at' => now(),
            'description' => 'Faux résidu (ancien comportement)',
        ]);

        $calculator->calculateForPeriod($period, $leader->id);

        $pendingMissing = Commission::query()
            ->where('user_id', $leader->id)
            ->where('from_user_id', $missing->id)
            ->where('type', 'indirect')
            ->where('status', 'pending')
            ->sum('amount');

        $pendingOther = Commission::query()
            ->where('user_id', $leader->id)
            ->where('from_user_id', $other->id)
            ->where('type', 'indirect')
            ->where('status', 'pending')
            ->sum('amount');

        $this->assertEqualsWithDelta($expectedMissing, (float) $pendingMissing, 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $pendingOther, 0.001);
    }
}
