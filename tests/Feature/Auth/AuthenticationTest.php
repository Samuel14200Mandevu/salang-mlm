<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Email, ID membre ou téléphone', false);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_authenticate_with_sponsor_id(): void
    {
        $user = User::factory()->create([
            'sponsor_id' => '511739',
        ]);

        $response = $this->post('/login', [
            'login' => '511739',
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_authenticate_with_phone(): void
    {
        $user = User::factory()->create([
            'phone' => '+243 810 000 111',
        ]);

        $response = $this->post('/login', [
            'login' => '243810000111',
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_phone_shared_by_several_accounts_requires_choice(): void
    {
        $first = User::factory()->create([
            'name' => 'Compte A',
            'phone' => '+243 810 000 333',
            'sponsor_id' => '511101',
        ]);
        $second = User::factory()->create([
            'name' => 'Compte B',
            'phone' => '+243 810 000 333',
            'sponsor_id' => '511102',
        ]);

        $response = $this->post('/login', [
            'login' => '243810000333',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login.choose-account', absolute: false));
        $this->assertGuest();
        $response->assertSessionHas(\App\Http\Requests\Auth\LoginRequest::PENDING_USER_IDS);

        $choose = $this->get(route('login.choose-account'));
        $choose->assertOk();
        $choose->assertSee('Compte A', false);
        $choose->assertSee('Compte B', false);
        $choose->assertSee('511101', false);

        $finish = $this->post(route('login.choose-account.store'), [
            'user_id' => $second->id,
        ]);

        $this->assertAuthenticatedAs($second);
        $finish->assertRedirect(route('dashboard', absolute: false));
        $this->assertNull(session(\App\Http\Requests\Auth\LoginRequest::PENDING_USER_IDS));
        unset($first);
    }

    public function test_login_still_accepts_legacy_email_field(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_accepts_remember_me_checkbox(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
            'remember' => 'on',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
