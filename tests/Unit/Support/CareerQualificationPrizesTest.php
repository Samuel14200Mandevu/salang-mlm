<?php

namespace Tests\Unit\Support;

use App\Models\User;
use App\Support\CareerQualificationPrizes;
use Tests\TestCase;

class CareerQualificationPrizesTest extends TestCase
{
    public function test_for_wallet_marks_unlocked_levels(): void
    {
        $user = new User(['rank_level' => 6]);

        $items = CareerQualificationPrizes::forWallet($user);

        $this->assertCount(6, $items);
        $this->assertTrue(collect($items)->firstWhere('level', 4)['unlocked']);
        $this->assertTrue(collect($items)->firstWhere('level', 6)['unlocked']);
        $this->assertFalse(collect($items)->firstWhere('level', 7)['unlocked']);
    }

    public function test_level_five_has_smart_tv_image_path(): void
    {
        $user = new User(['rank_level' => 1]);
        $level5 = collect(CareerQualificationPrizes::forWallet($user))->firstWhere('level', 5);

        $this->assertStringContainsString('smarttv', $level5['image_url'] ?? '');
    }

    public function test_level_four_uses_fifteen_dollar_image(): void
    {
        $user = new User(['rank_level' => 1]);
        $level4 = collect(CareerQualificationPrizes::forWallet($user))->firstWhere('level', 4);

        $this->assertNotNull($level4['image_url']);
        $this->assertStringContainsString('15', $level4['image_url']);
    }
}
