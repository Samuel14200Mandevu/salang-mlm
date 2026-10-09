<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PurchaseTest extends DuskTestCase
{

    public function test_shop_page_loads_for_authenticated_user(): void
    {
        $user = $this->duskUser();

        $this->browse(function (Browser $browser) use ($user) {
            $this->actingAsInBrowser($browser, $user)
                ->visit('/products')
                ->assertPathIs('/products');
        });
    }
}
