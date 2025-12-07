<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminSignInTest extends DuskTestCase
{
    public function testAdminCanSignInSuccessfully()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/auth/sign-in')
                ->pause(2000)
                ->screenshot('01-admin-sign-in-page')
                ->assertPathIs('/auth/sign-in');

            $browser->type('email', 'admin@admin')
                ->type('password', '123123123')
                ->screenshot('02-credentials-entered');

            $browser->press('Masuk')
                ->screenshot('03-after-submit');

            $browser->pause(3000)
                ->screenshot('04-after-redirect');

            $browser->assertPathIsNot('/auth/sign-in');

            $browser->assertPathIs('/admin')
                ->screenshot('05-admin-dashboard');

            $browser->assertSee('Admin 1')
                ->screenshot('06-admin-name-displayed');
        });
    }
}
