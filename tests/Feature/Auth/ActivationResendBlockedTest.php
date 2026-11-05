<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ActivationResendBlockedTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_resend_activation_code(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'is_active' => false,
            'activation_code' => null,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('activate.resend'), [
            'method' => 'email',
        ]);

        $response->assertRedirect(route('activate.index'));
        $response->assertSessionHas('error', 'Seul l\'administrateur peut renvoyer le code.');

        $user->refresh();
        $this->assertNull($user->activation_code);
        Notification::assertNothingSent();
    }
}
