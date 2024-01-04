<?php

namespace Tests\Feature\Api\DataCollector;

use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    /** @test */
    public function test_store_call_returns_ok()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson(route('api.data-collector.store'), [
                'warehouse_id' => Warehouse::factory()->create()->getKey(),
                'name' => 'test',
            ]);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'created_at',
                'updated_at',
            ],
        ]);
    }
}
