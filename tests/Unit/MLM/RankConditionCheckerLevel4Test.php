<?php

namespace Tests\Unit\MLM;

use App\Models\Rank;
use App\Models\User;
use App\Services\MLM\RankConditionChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RankConditionCheckerLevel4Test extends TestCase
{
    use RefreshDatabase;

    private RankConditionChecker $checker;

    /** @var array<int, Rank> */
    private array $ranksByLevel = [];

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->checker = app(RankConditionChecker::class);
        foreach (range(1, 4) as $level) {
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

    private function sponsor(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'rank_id' => $this->ranksByLevel[3]->id,
            'rank_level' => 3,
            'is_active' => true,
            'team_pv' => 0,
            'monthly_pv' => 0,
            'pv_balance' => 200,
            'rank4_grandfathered' => false,
        ], $overrides));
    }

    public function test_grandfathered_user_passes_level_4_without_conditions(): void
    {
        $user = $this->sponsor(['rank4_grandfathered' => true]);
        $this->assertTrue($this->checker->checkConditions($user, $this->ranksByLevel[4]));
    }

    public function test_solo_1000_monthly_pv_without_referrals_qualifies(): void
    {
        $user = $this->sponsor(['monthly_pv' => 1000, 'pv_balance' => 1000]);
        $this->assertTrue($this->checker->checkConditions($user, $this->ranksByLevel[4]));
    }

    public function test_1000_lifetime_pv_over_months_without_referrals_does_not_qualify(): void
    {
        $user = $this->sponsor([
            'pv_balance' => 1000,
            'monthly_pv' => 100,
        ]);
        $this->assertFalse($this->checker->checkConditions($user, $this->ranksByLevel[4]));
    }

    public function test_one_referral_with_1000_cumul_stays_below_level_4(): void
    {
        $sponsor = $this->sponsor(['team_pv' => 1000, 'monthly_pv' => 1000]);
        User::factory()->create([
            'parrain_id' => $sponsor->id,
            'rank_id' => $this->ranksByLevel[3]->id,
            'rank_level' => 3,
            'is_active' => true,
        ]);
        $sponsor->refresh();

        $this->assertFalse($this->checker->checkConditions($sponsor, $this->ranksByLevel[4]));
    }

    public function test_one_qualified_branch_with_2200_cumul_qualifies(): void
    {
        $sponsor = $this->sponsor(['team_pv' => 2200]);
        User::factory()->create([
            'parrain_id' => $sponsor->id,
            'rank_id' => $this->ranksByLevel[3]->id,
            'rank_level' => 3,
            'is_active' => true,
        ]);
        $sponsor->refresh();

        $this->assertTrue($this->checker->checkConditions($sponsor, $this->ranksByLevel[4]));
    }

    public function test_three_qualified_branches_with_1000_cumul_qualifies(): void
    {
        $sponsor = $this->sponsor(['team_pv' => 1000]);
        foreach (range(1, 3) as $_) {
            User::factory()->create([
                'parrain_id' => $sponsor->id,
                'rank_id' => $this->ranksByLevel[3]->id,
                'rank_level' => 3,
                'is_active' => true,
            ]);
        }
        $sponsor->refresh();

        $this->assertTrue($this->checker->checkConditions($sponsor, $this->ranksByLevel[4]));
    }
}
