<?php

namespace Tests\Unit\MLM;

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
 * Si un filleul (direct ou indirect) d’une jambe a un grade ≥ l’upline,
 * toute la jambe est hors bonus indirect ; les autres jambes restent éligibles.
 */
class IndirectFullBranchExclusionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_same_grade_anywhere_in_leg_blocks_entire_branch_for_indirect(): void
    {
        $period = CommissionPeriod::firstOrCreate(
            ['period' => MlmPeriod::current()],
            [
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'status' => 'pending',
            ]
        );

        // Upline grade 7
        $leader = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'rank_level' => 7,
            'rank' => 'Saphire Manager',
            'team_pv' => 200000,
            'sponsor_id' => '900001',
        ]);

        // Jambe A : mid plus bas + peer même grade + cousin plus bas
        $midA = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $leader->id,
            'rank_level' => 5,
            'rank' => 'Manager Senior',
            'team_pv' => 10000,
            'sponsor_id' => '900002',
        ]);
        $peerSameGrade = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $midA->id,
            'rank_level' => 7,
            'rank' => 'Saphire Manager',
            'team_pv' => 5000,
            'sponsor_id' => '900003',
        ]);
        $cousinLower = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $midA->id,
            'rank_level' => 4,
            'rank' => 'Directeur',
            'team_pv' => 1000,
            'sponsor_id' => '900004',
        ]);

        // Jambe B : pas de peer ≥ 7 → indirect OK
        $midB = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $leader->id,
            'rank_level' => 5,
            'rank' => 'Manager Senior',
            'team_pv' => 8000,
            'sponsor_id' => '900005',
        ]);
        $deepB = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $midB->id,
            'rank_level' => 4,
            'rank' => 'Directeur',
            'team_pv' => 500,
            'sponsor_id' => '900006',
        ]);

        foreach ([
            [$leader, 100],
            [$midA, 100],
            [$peerSameGrade, 100],
            [$cousinLower, 100],
            [$midB, 100],
            [$deepB, 100],
        ] as [$user, $pv]) {
            PVHistory::create([
                'user_id' => $user->id,
                'amount' => $pv,
                'date' => now()->toDateString(),
                'period' => $period->period,
                'type' => 'personal',
                'status' => 'posted',
                'notes' => 'test full-branch exclusion',
                'created_by' => $leader->id,
            ]);
        }

        app(PeriodCommissionCalculator::class)->calculateForPeriod($period, $leader->id);

        $indirectFrom = Commission::query()
            ->where('user_id', $leader->id)
            ->where('period', $period->period)
            ->where('type', 'indirect')
            ->pluck('from_user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        // Jambe A entièrement hors indirect (même le mid et le cousin)
        $this->assertNotContains($midA->id, $indirectFrom);
        $this->assertNotContains($peerSameGrade->id, $indirectFrom);
        $this->assertNotContains($cousinLower->id, $indirectFrom);

        // Jambe B toujours éligible
        $this->assertContains($midB->id, $indirectFrom);
        $this->assertContains($deepB->id, $indirectFrom);
    }
}
