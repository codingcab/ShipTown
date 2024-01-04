<?php

namespace Tests\Feature\Api\MailTemplates;

use App\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_returns_ok()
    {
        Passport::actingAs(
            User::factory()->admin()->create()
        );

        $response = $this->get(route('api.mail-templates.index'));

        $response->assertSuccessful();
    }

    public function test_index_call_should_be_loggedin()
    {
        $response = $this->get(route('api.mail-templates.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_call_should_loggedin_as_admin()
    {
        Passport::actingAs(
            User::factory()->create()
        );

        $response = $this->get(route('api.mail-templates.index'));

        $response->assertForbidden();
    }
}
