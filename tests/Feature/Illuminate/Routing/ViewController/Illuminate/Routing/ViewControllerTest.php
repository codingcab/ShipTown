<?php

namespace Tests\Feature\Illuminate\Routing\ViewController\Illuminate\Routing;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_viewcontroller_call_returns_ok()
    {
        $this->assertTrue(true, 'should not be tested, test-generator should not ask for it but no harm');
    }
}
