<?php

namespace Tests\Feature\QuickConnect;

use App\User;
use Tests\TestCase;

/**
 *
 */
class MagentoTest extends TestCase
{
    /**
     * @var string
     */
    protected string $uri = '/quick-connect/magento';

    protected mixed $user;

    protected function setUp(): void
    {
        parent::setUp();
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
        /** @var User user */
        $user = User::factory()->create();

        $this->actingAs($user, 'web');

        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }

    /** @test */
    public function test_admin_call()
    {
        /** @var User user */
        $user = User::factory()->create();

        $user->assignRole('admin');

        $this->actingAs($user, 'web');

        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }
}
