<?php

namespace Tests\Feature\Pv;

use App\Models\User;
use App\Services\PvManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PvNetworkSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_network_member_by_code_accepts_deep_downline(): void
    {
        $root = User::factory()->create([
            'user_type' => 'member',
            'sponsor_id' => '511642',
            'phone' => 'root-' . Str::random(6),
        ]);

        $level1 = User::factory()->create([
            'user_type' => 'member',
            'sponsor_id' => '611001',
            'parrain_id' => $root->id,
            'phone' => 'l1-' . Str::random(6),
        ]);

        $level3 = User::factory()->create([
            'user_type' => 'member',
            'sponsor_id' => '611003',
            'parrain_id' => User::factory()->create([
                'user_type' => 'member',
                'sponsor_id' => '611002',
                'parrain_id' => $level1->id,
                'phone' => 'l2-' . Str::random(6),
            ])->id,
            'phone' => 'l3-' . Str::random(6),
        ]);

        $service = app(PvManagementService::class);

        $this->assertSame(3, $service->networkDepth($root, $level3));

        $found = $service->findNetworkMemberByCode($root, '611003');
        $this->assertTrue($found['ok']);
        $this->assertSame($level3->id, $found['member']['id']);
        $this->assertSame(3, $found['member']['level']);
    }

    public function test_find_network_member_by_code_rejects_outsider(): void
    {
        $root = User::factory()->create([
            'user_type' => 'member',
            'sponsor_id' => '700001',
            'phone' => 'root-' . Str::random(6),
        ]);

        $outsider = User::factory()->create([
            'user_type' => 'member',
            'sponsor_id' => '700999',
            'phone' => 'out-' . Str::random(6),
        ]);

        $service = app(PvManagementService::class);
        $result = $service->findNetworkMemberByCode($root, '700999');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('ne fait pas partie du réseau', $result['message']);
        $this->assertNull($service->networkDepth($root, $outsider));
    }
}
