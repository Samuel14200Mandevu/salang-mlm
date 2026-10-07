<?php

namespace Tests\Unit\MLM;

use App\Models\Rank;
use App\Models\User;
use App\Services\MLM\RankConditionChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankConditionCheckerMixedBranchesTest extends TestCase
{
    use RefreshDatabase;

    private RankConditionChecker $checker;

    /** @var array<int, Rank> */
    private array $ranksByLevel = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = app(RankConditionChecker::class);
        $this->seedRanks();
    }

    private function seedRanks(): void
    {
        foreach (range(1, 6) as $level) {
            $this->ranksByLevel[$level] = Rank::create([
                'level' => $level,
                'name' => 'Rank '.$level,
                'slug' => 'rank-'.$level,
                'min_pv' => 0,
                'min_bv' => 0,
                'bonus_percentage' => 0,
                'is_active' => true,
            ]);
        }
    }

    private function userWithRank(int $level, ?int $parrainId = null): User
    {
        return User::factory()->create([
            'parrain_id' => $parrainId,
            'rank_id' => $this->ranksByLevel[$level]->id,
            'rank_level' => $level,
            'is_active' => true,
        ]);
    }

    public function test_branch_qualifies_from_descendant_rank_not_only_direct_child(): void
    {
        $sponsor = $this->userWithRank(1);
        $direct = $this->userWithRank(3, $sponsor->id);
        $this->userWithRank(4, $direct->id);

        $this->assertTrue(
            $this->checker->satisfiesExclusiveMixedBranches($sponsor, [1 => 4])
        );
    }

    public function test_mixed_branches_do_not_reuse_same_leg_for_two_tiers(): void
    {
        $sponsor = $this->userWithRank(1);

        $legA = $this->userWithRank(5, $sponsor->id);
        $legB = $this->userWithRank(5, $sponsor->id);
        $this->userWithRank(4, $sponsor->id);
        $this->userWithRank(4, $sponsor->id);

        unset($legA, $legB);

        $this->assertFalse(
            $this->checker->satisfiesExclusiveMixedBranches($sponsor, [2 => 5, 4 => 4])
        );
    }

    public function test_mixed_branches_succeed_with_six_disjoint_legs(): void
    {
        $sponsor = $this->userWithRank(1);

        $this->userWithRank(5, $sponsor->id);
        $this->userWithRank(5, $sponsor->id);

        $legC = $this->userWithRank(3, $sponsor->id);
        $this->userWithRank(4, $legC->id);

        $legD = $this->userWithRank(3, $sponsor->id);
        $this->userWithRank(4, $legD->id);

        $this->userWithRank(4, $sponsor->id);
        $this->userWithRank(4, $sponsor->id);

        $this->assertTrue(
            $this->checker->satisfiesExclusiveMixedBranches($sponsor, [2 => 5, 4 => 4])
        );
    }
}
