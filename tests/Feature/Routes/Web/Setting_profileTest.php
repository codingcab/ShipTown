<?php

namespace Tests\Feature\Routes\Web;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 *
 */
class SettingProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var string
     */
    protected string $uri = '';

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
        $this->user = factory(User::class)->create();
        $this->actingAs($this->user, 'web');
    }

    /** @test */
    public function test_admin_call()
    {
        $this->markTestIncomplete('This test was generated by "php artisan app:generate-routes-tests" call');

        $this->user->assignRole('admin');

        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }

    /** @test */
    public function test_user_call()
    {
        $this->markTestIncomplete('This test was generated by "php artisan app:generate-routes-tests" call');

        $response = $this->get($this->uri);

        $response->assertForbidden();
    }
}
