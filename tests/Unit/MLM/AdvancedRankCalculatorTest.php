<?php

namespace Tests\Unit\MLM;

use App\Models\User;
use App\Services\MLM\AdvancedRankCalculator;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class AdvancedRankCalculatorTest extends TestCase
{
    protected AdvancedRankCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new AdvancedRankCalculator();
        // Ne pas recréer les ranks, ils sont déjà dans la base
    }

    public function test_user_with_0_pv_gets_distributor_rank(): void
    {
        $user = User::factory()->create(['pv_balance' => 0, 'bv_balance' => 0]);
        $rank = $this->calculator->calculateAdvancedRank($user);
        $this->assertNotNull($rank);
        $this->assertEquals('Distributeur', $rank->name);
    }

    public function test_user_with_100_pv_gets_qualification_rank(): void
    {
        $user = User::factory()->create(['pv_balance' => 100, 'bv_balance' => 100]);
        $rank = $this->calculator->calculateAdvancedRank($user);
        $this->assertNotNull($rank);
        $this->assertEquals('Qualification', $rank->name);
    }

    public function test_user_with_200_pv_gets_assistant_manager_rank(): void
    {
        $user = User::factory()->create(['pv_balance' => 200, 'bv_balance' => 200]);
        $rank = $this->calculator->calculateAdvancedRank($user);
        $this->assertNotNull($rank);
        $this->assertEquals('Cumul Directeur', $rank->name);
    }

    public function test_user_with_1000_pv_gets_manager_rank(): void
    {
        $user = User::factory()->create(['pv_balance' => 1000, 'bv_balance' => 1000]);
        $rank = $this->calculator->calculateAdvancedRank($user);
        $this->assertNotNull($rank);
        $this->assertEquals('Directeur', $rank->name);
    }

    public function test_user_with_3800_pv_gets_senior_manager_rank(): void
    {
        $user = User::factory()->create(['pv_balance' => 3800, 'bv_balance' => 3800]);
        $rank = $this->calculator->calculateAdvancedRank($user);
        $this->assertNotNull($rank);
        $this->assertEquals('Manager Senior', $rank->name);
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
