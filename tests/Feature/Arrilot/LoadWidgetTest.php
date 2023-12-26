<?php

namespace Tests\Feature\Arrilot;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 *
 */
class LoadWidgetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var string
     */
    protected string $uri = '/arrilot/load-widget';

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
    public function test_if_uri_set()
    {
        $this->assertNotEmpty($this->uri);
    }
}
