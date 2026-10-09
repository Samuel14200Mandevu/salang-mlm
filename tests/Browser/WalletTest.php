<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class WalletTest extends DuskTestCase
{

    public function test_wallet_page_shows_balance_section(): void
    {
        $user = $this->duskUser();

        $this->browse(function (Browser $browser) use ($user) {
            $this->actingAsInBrowser($browser, $user)
                ->visit('/wallet')
                ->assertPathIs('/wallet')
                ->assertSee('Mon portefeuille');
        });
    }
}
