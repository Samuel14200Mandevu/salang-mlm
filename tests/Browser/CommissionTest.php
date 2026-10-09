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
            $this->actingAsInBrowser($browser, $user)
                ->visit('/commissions')
                ->assertPathIs('/commissions');
        });
    }
}
