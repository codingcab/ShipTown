<?php

namespace Tests\Feature\Api\Modules\AutoStatus\Picking\Configuration;

use App\Models\AutoStatusPickingConfiguration;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_store_call_returns_ok()
    {
        $configuration = AutoStatusPickingConfiguration::factory()->make();

        $response = $this->postJson(route('modules.autostatus.picking.configuration.store'), $configuration->toArray());

        $response->assertSuccessful();
    }
}
