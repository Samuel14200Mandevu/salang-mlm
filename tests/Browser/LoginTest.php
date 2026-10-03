<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{

    public function test_user_can_login_and_see_dashboard(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('#email', $user->email)
                ->type('#password', 'password')
                ->press('Se connecter')
                ->waitForLocation('/dashboard', 10)
                ->assertSee($user->name);
        });
    }
}
