<?php

namespace Tests\Feature\Auth;

use App\Models\Rank;
use App\Models\User;
use App\Notifications\ActivationCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleSocialiteTest extends TestCase
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

    public function test_login_google_redirect_does_not_require_sponsor(): void
    {
        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($driver = Mockery::mock());

        $driver->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        $response = $this->get('/auth/google?intent=login');

        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
        $response->assertSessionHas('socialite_intent', 'login');
    }

    public function test_register_google_redirect_without_sponsor_returns_to_register(): void
    {
        $response = $this->get('/auth/google');

        $response->assertRedirect('/register');
        $response->assertSessionHas('error');
    }

    public function test_google_login_callback_logs_in_existing_user(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'is_active' => true,
            'password' => bcrypt('password'),
        ]);

        $socialUser = Mockery::mock(SocialiteUserContract::class);
        $socialUser->shouldReceive('getEmail')->andReturn('member@example.com');
        $socialUser->shouldReceive('getId')->andReturn('google-sub-1');
        $socialUser->shouldReceive('getAvatar')->andReturn(null);
        $socialUser->shouldReceive('getName')->andReturn('Member Test');

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($driver = Mockery::mock());

        $driver->shouldReceive('user')->once()->andReturn($socialUser);

        $response = $this->withSession(['socialite_intent' => 'login'])
            ->get('/auth/google/callback');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_login_callback_unknown_email_returns_to_login(): void
    {
        $socialUser = Mockery::mock(SocialiteUserContract::class);
        $socialUser->shouldReceive('getEmail')->andReturn('unknown@example.com');
        $socialUser->shouldReceive('getId')->andReturn('google-sub-2');

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($driver = Mockery::mock());

        $driver->shouldReceive('user')->once()->andReturn($socialUser);

        $response = $this->withSession(['socialite_intent' => 'login'])
            ->get('/auth/google/callback');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_google_registration_creates_inactive_user_and_sends_activation_notification(): void
    {
        Notification::fake();

        $sponsor = User::factory()->create([
            'sponsor_id' => '511001',
            'is_active' => true,
        ]);

        $socialUser = Mockery::mock(SocialiteUserContract::class);
        $socialUser->shouldReceive('getEmail')->andReturn('newgoogle@example.com');
        $socialUser->shouldReceive('getId')->andReturn('google-sub-new');
        $socialUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
        $socialUser->shouldReceive('getName')->andReturn('Google New User');
        $socialUser->shouldReceive('getNickname')->andReturn(null);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($driver = Mockery::mock());

        $driver->shouldReceive('user')->once()->andReturn($socialUser);

        $response = $this->withSession(['sponsor_id' => $sponsor->id, 'social_provider' => 'google'])
            ->get('/auth/google/callback');

        $user = User::where('email', 'newgoogle@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->is_active);
        $this->assertNotNull($user->activation_code);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('activate.index'));

        Notification::assertSentTo($user, ActivationCodeNotification::class);
    }
}
