<?php

namespace Tests\Unit\MLM;

use App\Models\User;
use App\Models\Rank;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedRankCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected AdvancedRankCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = app(AdvancedRankCalculator::class);
        $this->seedRanks();
    }

    private function seedRanks(): void
    {
        $ranks = [
            ['level' => 1, 'name' => 'Distributeur', 'slug' => 'distributor', 'min_pv' => 0, 'min_bv' => 0, 'bonus_percentage' => 6, 'is_active' => true],
            ['level' => 2, 'name' => 'Qualification', 'slug' => 'qualification', 'min_pv' => 100, 'min_bv' => 100, 'bonus_percentage' => 6, 'is_active' => true],
            ['level' => 3, 'name' => 'Cumul Directeur', 'slug' => 'assistant-manager', 'min_pv' => 200, 'min_bv' => 200, 'bonus_percentage' => 22, 'is_active' => true],
            ['level' => 4, 'name' => 'Directeur', 'slug' => 'manager', 'min_pv' => 1000, 'min_bv' => 1000, 'bonus_percentage' => 26, 'is_active' => true],
            ['level' => 5, 'name' => 'Manager Senior', 'slug' => 'senior-manager', 'min_pv' => 3800, 'min_bv' => 3800, 'bonus_percentage' => 30, 'is_active' => true],
        ];

        foreach ($ranks as $rankData) {
            Rank::create($rankData);
        }
    }

    public function test_user_with_0_pv_gets_distributor_rank(): void
    {
        $user = User::factory()->create([
            'pv_balance' => 0,
            'bv_balance' => 0,
            'team_pv' => 0,
            'team_bv' => 0,
            'is_active' => true,
        ]);
        $rank = $this->calculator->calculateAdvancedRank($user);
        $this->assertNotNull($rank);
        $this->assertEquals('Distributeur', $rank->name);
    }

    public function test_user_with_100_pv_gets_qualification_rank(): void
    {
        $user = User::factory()->create([
            'pv_balance' => 100,
            'bv_balance' => 100,
            'team_pv' => 100,
            'team_bv' => 100,
            'is_active' => true,
        ]);
        $rank = $this->calculator->calculateAdvancedRank($user);
        $this->assertNotNull($rank);
        $this->assertEquals('Qualification', $rank->name);
    }

    public function test_user_with_200_pv_gets_assistant_manager_rank(): void
    {
        $user = User::factory()->create([
            'pv_balance' => 200,
            'bv_balance' => 200,
            'team_pv' => 200,
            'team_bv' => 200,
            'is_active' => true,
        ]);
        $rank = $this->calculator->calculateAdvancedRank($user);
        $this->assertNotNull($rank);
        $this->assertEquals('Cumul Directeur', $rank->name);
    }

    public function test_user_with_1000_lifetime_pv_without_monthly_stays_assistant_manager(): void
    {
        $user = User::factory()->create([
            'pv_balance' => 1000,
            'bv_balance' => 1000,
            'monthly_pv' => 0,
            'team_pv' => 0,
            'team_bv' => 0,
            'is_active' => true,
        ]);
        $rank = $this->calculator->calculateAdvancedRank($user);
        $this->assertNotNull($rank);
        $this->assertEquals('Cumul Directeur', $rank->name);
    }

    public function test_user_with_1000_monthly_pv_and_no_referrals_gets_directeur(): void
    {
        $user = User::factory()->create([
            'pv_balance' => 1000,
            'monthly_pv' => 1000,
            'team_pv' => 0,
            'is_active' => true,
        ]);
        $rank = $this->calculator->calculateAdvancedRank($user);
        $this->assertEquals('Directeur', $rank->name);
    }

    public function test_user_with_3800_personal_pv_gets_directeur_rank(): void
    {
        $user = User::factory()->create([
            'pv_balance' => 3800,
            'bv_balance' => 3800,
            'monthly_pv' => 3800,
            'team_pv' => 0,
            'team_bv' => 0,
            'is_active' => true,
        ]);
        $rank = $this->calculator->calculateAdvancedRank($user);
        $this->assertNotNull($rank);
        // Manager Senior (niv. 5) exige des branches qualifiées ; seul le PV personnel → Directeur.
        $this->assertEquals('Directeur', $rank->name);
    }

    public function test_inactive_user_gets_no_rank(): void
    {
        $user = User::factory()->create([
            'pv_balance' => 1000,
            'bv_balance' => 1000,
            'is_active' => 0,
        ]);
        $rank = $this->calculator->calculateAdvancedRank($user);
        $this->assertNull($rank);
    }
}
