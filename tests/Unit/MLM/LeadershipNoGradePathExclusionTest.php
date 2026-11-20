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
 * Leadership : max 12 générations, sans coupure par grade sur le chemin
 * ni par branch block. L'exclusion jambe (`is_branch_blocked`) et chemin
 * (`is_excluded`) restent réservées à l'Indirect.
 */
class LeadershipNoGradePathExclusionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_same_grade_on_path_blocks_indirect_but_not_leadership(): void
    {
        $period = $this->makePeriod();

        // Upline grade 7 (taux L = 1.8 %)
        $leader = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'rank_level' => 7,
            'rank' => 'Saphire Manager',
            'team_pv' => 200000,
            'sponsor_id' => '910001',
        ]);

        // Nœud intermédiaire même grade → chemin bloqué pour Indirect
        $peerSameGrade = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $leader->id,
            'rank_level' => 7,
            'rank' => 'Saphire Manager',
            'team_pv' => 5000,
            'sponsor_id' => '910002',
        ]);

        // Descendant gén. 2 avec PV (sous le peer grade 7 → is_excluded)
        $deep = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $peerSameGrade->id,
            'rank_level' => 4,
            'rank' => 'Directeur',
            'team_pv' => 500,
            'sponsor_id' => '910003',
        ]);

        foreach ([[$leader, 100], [$peerSameGrade, 100], [$deep, 100]] as [$user, $pv]) {
            $this->postPv($user, $pv, $period, $leader->id);
        }

        app(PeriodCommissionCalculator::class)->calculateForPeriod($period, $leader->id);

        $indirectFrom = $this->commissionFromIds($leader->id, $period->period, 'indirect');
        $leadershipFrom = $this->commissionFromIds($leader->id, $period->period, 'leadership');

        // Indirect : coupé (g≥7 sur le chemin)
        $this->assertNotContains($peerSameGrade->id, $indirectFrom);
        $this->assertNotContains($deep->id, $indirectFrom);

        // Leadership : dû malgré grades 7 sur le chemin (gén. ≤ 12)
        $this->assertContains($peerSameGrade->id, $leadershipFrom);
        $this->assertContains($deep->id, $leadershipFrom);

        $deepL = Commission::query()
            ->where('user_id', $leader->id)
            ->where('period', $period->period)
            ->where('type', 'leadership')
            ->where('from_user_id', $deep->id)
            ->first();

        $this->assertNotNull($deepL);
        $this->assertSame(2, (int) $deepL->generation);
        $this->assertEqualsWithDelta(1.8, (float) $deepL->percentage, 0.0001);
        $this->assertEqualsWithDelta(1.8, (float) $deepL->amount, 0.0001); // 100 PV × 1.8 %
    }

    public function test_leadership_stops_after_generation_12(): void
    {
        $period = $this->makePeriod();

        $leader = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'rank_level' => 7,
            'rank' => 'Saphire Manager',
            'team_pv' => 200000,
            'sponsor_id' => '920001',
        ]);

        $parent = $leader;
        $chain = [];
        for ($gen = 1; $gen <= 13; $gen++) {
            $node = User::factory()->create([
                'is_active' => true,
                'user_type' => 'member',
                'parrain_id' => $parent->id,
                // Grades bas : pas de blocage Indirect ; on teste seulement la profondeur L
                'rank_level' => 3,
                'rank' => 'Supervisor',
                'team_pv' => 100,
                'sponsor_id' => (string) (920001 + $gen),
            ]);
            $chain[$gen] = $node;
            $parent = $node;
        }

        $this->postPv($leader, 100, $period, $leader->id);
        $this->postPv($chain[12], 100, $period, $leader->id);
        $this->postPv($chain[13], 100, $period, $leader->id);

        app(PeriodCommissionCalculator::class)->calculateForPeriod($period, $leader->id);

        $leadershipFrom = $this->commissionFromIds($leader->id, $period->period, 'leadership');

        $this->assertContains($chain[12]->id, $leadershipFrom);
        $this->assertNotContains($chain[13]->id, $leadershipFrom);
    }

    private function makePeriod(): CommissionPeriod
    {
        return CommissionPeriod::firstOrCreate(
            ['period' => MlmPeriod::current()],
            [
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'status' => 'pending',
            ]
        );
    }

    private function postPv(User $user, int $amount, CommissionPeriod $period, int $createdBy): void
    {
        PVHistory::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'date' => now()->toDateString(),
            'period' => $period->period,
            'type' => 'personal',
            'status' => 'posted',
            'notes' => 'test leadership no grade path exclusion',
            'created_by' => $createdBy,
        ]);
    }

    /**
     * @return list<int>
     */
    private function commissionFromIds(int $userId, string $period, string $type): array
    {
        return Commission::query()
            ->where('user_id', $userId)
            ->where('period', $period)
            ->where('type', $type)
            ->pluck('from_user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
