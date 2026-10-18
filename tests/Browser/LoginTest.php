<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{

    public function test_user_can_login_and_reach_dashboard(): void
    {
        $user = $this->duskUser();

        $this->browse(function (Browser $browser) use ($user) {
            $this->loginViaBrowser($browser, $user)
                ->assertPathIs('/dashboard');
        });
    }
}
