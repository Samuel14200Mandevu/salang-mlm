<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PurchaseTest extends DuskTestCase
{

    public function test_shop_page_loads_for_authenticated_user(): void
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
                ->visit('/products')
                ->assertPathIs('/products');
        });
    }
}
