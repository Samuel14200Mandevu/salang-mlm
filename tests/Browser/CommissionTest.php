<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CommissionTest extends DuskTestCase
{

    public function test_commissions_index_is_accessible(): void
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
                ->visit('/commissions')
                ->assertPathIs('/commissions');
        });
    }
}
