<?php

namespace Tests\Feature\Auth;

use App\Models\Rank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        User::factory()->create([
            'sponsor_id' => '511001',
            'is_active' => true,
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'sponsor_id' => '511001',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('activate.index', absolute: false));
    }
}
