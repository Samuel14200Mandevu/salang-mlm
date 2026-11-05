<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MagicLinkActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_magic_link_activates_logs_in_and_redirects_to_dashboard(): void
    {
        $code = 'ACT-TESTMAGIC01';

        $user = User::factory()->create([
            'email' => 'magic-activate@example.com',
            'is_active' => false,
            'activation_code' => $code,
            'activation_code_expires_at' => now()->addDays(7),
            'email_verified_at' => now(),
        ]);

        $response = $this->get(route('activate.account', ['code' => $code]));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame($user->id, Auth::id());

        $user->refresh();
        $this->assertTrue((bool) $user->is_active);
        $this->assertNull($user->activation_code);
        $this->assertNull($user->activation_code_expires_at);
        $this->assertSame('link', $user->activation_method);

        // Follow redirect with same session cookie (no re-actingAs)
        $follow = $this->get(route('dashboard'));
        $follow->assertOk();
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_invalid_magic_link_redirects_to_login_without_auth(): void
    {
        $response = $this->get(route('activate.account', ['code' => 'ACT-DOESNOTEXIST']));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_expired_magic_link_redirects_to_login_and_keeps_inactive(): void
    {
        $code = 'ACT-EXPIREDCODE1';

        $user = User::factory()->create([
            'is_active' => false,
            'activation_code' => $code,
            'activation_code_expires_at' => now()->subDay(),
            'email_verified_at' => now(),
        ]);

        $response = $this->get(route('activate.account', ['code' => $code]));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertFalse((bool) $user->fresh()->is_active);
        $this->assertSame($code, $user->fresh()->activation_code);
    }
}
