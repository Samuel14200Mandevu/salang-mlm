<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Services\MLM\CommissionHistoryToCommissionsSync;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoModeGuardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::factory()->create([
            'name' => 'Admin Demo Guard',
            'email' => 'admin-demo-guard@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');
    }

    public function test_sync_blocked_when_demo_mode_disabled(): void
    {
        config(['mlm.demo_mode_enabled' => false]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('désactivée en production');

        app(CommissionHistoryToCommissionsSync::class)->syncPeriods(collect(['2026-01']));
    }

    public function test_recalculate_blocked_when_demo_mode_disabled(): void
    {
        config(['mlm.demo_mode_enabled' => false]);

        $response = $this->actingAs($this->admin)->post(
            route('admin.pv.commission-history.recalculate'),
            ['period' => '2026-01']
        );

        $response->assertForbidden();
    }

    public function test_commission_history_index_allowed_when_demo_mode_disabled(): void
    {
        config(['mlm.demo_mode_enabled' => false]);

        $response = $this->actingAs($this->admin)->get(
            route('admin.pv.commission-history.index')
        );

        $response->assertOk();
    }
}
