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
 * Indirect : si un filleul (direct ou indirect) d’une jambe a un grade ≥ upline,
 * toute la jambe est hors bonus Indirect (`is_branch_blocked`).
 * Les autres jambes restent éligibles.
 *
 * Leadership ignore le branch block et l’exclusion chemin : max 12 gén. uniquement.
 */
class IndirectFullBranchExclusionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /**
     * Cas type 511642 ← … ← 513067 : peer g7 ailleurs dans la jambe
     * → pas d’Indirect sur toute la jambe ; Leadership dû (gén. ≤ 12).
     */
    public function test_same_grade_anywhere_in_leg_blocks_entire_branch_for_indirect(): void
    {
        $period = $this->makePeriod();

        // Upline grade 7 (taux direct 40 %, L 1.8 %)
        $leader = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'rank_level' => 7,
            'rank' => 'Saphire Manager',
            'team_pv' => 200000,
            'sponsor_id' => '900001',
        ]);

        // Jambe A : mid + peer même grade + cousin chemin propre
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

        // Jambe B : pas de peer ≥ 7 → Indirect OK
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
            $this->postPv($user, $pv, $period, $leader->id);
        }

        app(PeriodCommissionCalculator::class)->calculateForPeriod($period, $leader->id);

        $indirectFrom = $this->commissionFromIds($leader->id, $period->period, 'indirect');
        $leadershipFrom = $this->commissionFromIds($leader->id, $period->period, 'leadership');

        // Jambe A entièrement hors Indirect (même mid et cousin « chemin propre »)
        $this->assertNotContains($midA->id, $indirectFrom);
        $this->assertNotContains($peerSameGrade->id, $indirectFrom);
        $this->assertNotContains($cousinLower->id, $indirectFrom);

        // Jambe B toujours éligible à l’Indirect
        $this->assertContains($midB->id, $indirectFrom);
        $this->assertContains($deepB->id, $indirectFrom);

        // Leadership dû sur la jambe A malgré le peer g7 (gén. ≤ 12)
        $this->assertContains($midA->id, $leadershipFrom);
        $this->assertContains($peerSameGrade->id, $leadershipFrom);
        $this->assertContains($cousinLower->id, $leadershipFrom);
    }

    /**
     * Cas type 512427/514644 : g≥7 sur le chemin → Indirect coupé pour
     * le nœud et tout son sous-arbre (et toute la jambe) ; Leadership OK.
     */
    public function test_same_grade_on_path_blocks_indirect_for_subtree(): void
    {
        $period = $this->makePeriod();

        $leader = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'rank_level' => 7,
            'rank' => 'Saphire Manager',
            'team_pv' => 200000,
            'sponsor_id' => '901001',
        ]);

        // Nœud intermédiaire même grade → chemin / jambe bloqués pour Indirect
        $peerOnPath = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $leader->id,
            'rank_level' => 7,
            'rank' => 'Saphire Manager',
            'team_pv' => 5000,
            'sponsor_id' => '901002',
        ]);

        $deepUnderPeer = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $peerOnPath->id,
            'rank_level' => 4,
            'rank' => 'Directeur',
            'team_pv' => 500,
            'sponsor_id' => '901003',
        ]);

        // Autre jambe propre → Indirect OK
        $cleanMid = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
            'parrain_id' => $leader->id,
            'rank_level' => 5,
            'rank' => 'Manager Senior',
            'team_pv' => 8000,
            'sponsor_id' => '901004',
        ]);

        foreach ([
            [$leader, 100],
            [$peerOnPath, 100],
            [$deepUnderPeer, 100],
            [$cleanMid, 100],
        ] as [$user, $pv]) {
            $this->postPv($user, $pv, $period, $leader->id);
        }

        app(PeriodCommissionCalculator::class)->calculateForPeriod($period, $leader->id);

        $indirectFrom = $this->commissionFromIds($leader->id, $period->period, 'indirect');
        $leadershipFrom = $this->commissionFromIds($leader->id, $period->period, 'leadership');

        $this->assertNotContains($peerOnPath->id, $indirectFrom);
        $this->assertNotContains($deepUnderPeer->id, $indirectFrom);
        $this->assertContains($cleanMid->id, $indirectFrom);

        // Leadership dû malgré g7 sur le chemin (gén. ≤ 12)
        $this->assertContains($peerOnPath->id, $leadershipFrom);
        $this->assertContains($deepUnderPeer->id, $leadershipFrom);
        $this->assertContains($cleanMid->id, $leadershipFrom);
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
            'notes' => 'test indirect full-branch exclusion',
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
