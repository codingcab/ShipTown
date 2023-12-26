<?php

namespace Tests\Feature;

use App\Models\Configuration;
use App\User;
use Tests\TestCase;

/**
 *
 */
class VerifyTest extends TestCase
{
    protected string $uri = '/verify';

    protected mixed $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        Configuration::query()->updateOrCreate([], ['disable_2fa' => false]);
    }

    /** @test */
    public function test_if_uri_set()
    {
        $this->assertNotEmpty($this->uri);
    }

    /** @test */
    public function test_guest_call()
    {
        $response = $this->get($this->uri);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_user_call()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }

    /** @test */
    public function test_admin_call()
    {
        $this->user->assignRole('admin');

        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }
}
