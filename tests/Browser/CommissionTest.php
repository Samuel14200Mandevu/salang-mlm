<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CommissionTest extends DuskTestCase
{

    public function test_commissions_index_is_accessible(): void
    {
        $user = $this->duskUser();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('#email', $user->email)
                ->type('#password', 'password')
                ->press('Se connecter')
                ->waitForLocation('/services', 10)
                ->visit('/commissions')
                ->assertPathIs('/commissions');
        });
    }
}
