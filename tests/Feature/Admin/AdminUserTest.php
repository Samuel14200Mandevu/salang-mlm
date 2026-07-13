<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Rank;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole('admin');

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@test.com',
        ]);
    }

    private function createRanks(): void
    {
        Rank::create([
            'level' => 1,
            'name' => 'Distributeur',
            'slug' => 'distributor',
            'min_pv' => 0,
            'min_bv' => 0,
            'bonus_percentage' => 6,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_users_list(): void
    {
        $this->createRanks();
        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Test User');
        $response->assertSee('user@test.com');
    }

    public function test_admin_can_view_user_details(): void
    {
        $this->createRanks();
        $response = $this->actingAs($this->admin)->get('/admin/users/' . $this->user->id);

        $response->assertStatus(200);
        $response->assertSee('Test User');
        $response->assertSee('user@test.com');
    }

    public function test_admin_can_create_user(): void
    {
        $this->createRanks();
        $response = $this->actingAs($this->admin)->post('/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $this->createRanks();
        $response = $this->actingAs($this->admin)->put('/admin/users/' . $this->user->id, [
            'name' => 'Updated User',
            'email' => 'updated@test.com',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated User',
            'email' => 'updated@test.com',
        ]);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $this->createRanks();
        $this->assertTrue($this->user->is_active);

        $response = $this->actingAs($this->admin)->get('/admin/users/' . $this->user->id . '/toggle-status');

        $response->assertRedirect('/admin/users');

        $user = User::find($this->user->id);
        $this->assertFalse($user->is_active);
    }

    public function test_admin_can_delete_user(): void
    {
        $this->createRanks();
        $response = $this->actingAs($this->admin)->delete('/admin/users/' . $this->user->id);

        $response->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', [
            'id' => $this->user->id,
        ]);
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/users');

        $response->assertStatus(403);
    }
}