<?php

namespace Tests\Feature\Api\Run\Sync\Api2cart;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_index_sync_api2cart_call_returns_ok()
    {
        $response = $this->get(route('api2cart.index'));

        ray($response->getContent());

        $response->assertStatus(200);
    }
}
