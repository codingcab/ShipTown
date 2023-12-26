<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 *
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var string
     */
    protected string $uri = '/login';

    /**
     * @var User
     */
    protected User $user;

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_guest_call()
    {
        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }

    /** @test */
    public function test_user_call()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function test_admin_call()
    {
        $this->actingAs($this->user, 'web');

        $this->user->assignRole('admin');

        $response = $this->get($this->uri);

        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function test_if_uri_set()
    {
        $this->assertNotEmpty($this->uri);
    }
}
