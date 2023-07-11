<?php

namespace Tests\Browser\Routes\Admin\Modules\Slack;

use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class ConfigurationPageTest extends DuskTestCase
{
    private string $uri = '/admin/modules/slack/configuration';

    /**
     * @throws Throwable
     */
    public function testPage()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->disableFitOnFailure();
            $browser->loginAs($user);
            $browser->visit($this->uri);
            $browser->assertPathIs($this->uri);
            $browser->assertSourceMissing('Server Error');
        });
    }

    /**
     * @throws Throwable
     */
    public function testBasics()
    {
        $this->basicUserAccessTest($this->uri, false);
        $this->basicAdminAccessTest($this->uri, true);
        $this->basicGuestAccessTest($this->uri);
    }
}
