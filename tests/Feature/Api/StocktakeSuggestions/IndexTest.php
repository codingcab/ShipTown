<?php

namespace Tests\Feature\Api\StocktakeSuggestions;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StocktakeSuggestion;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_returns_ok()
    {
        $user = User::factory()->create();

        Product::factory()->create();
        Warehouse::factory()->create();

        StocktakeSuggestion::factory()->create(
            [
                'inventory_id' => Inventory::first()->getKey(),
                'points' => 1,
                'reason' => 'test',
            ]
        );

        $response = $this->actingAs($user, 'api')->getJson(route('api.stocktake-suggestions.index'));

        ray($response->json());

        $response->assertOk();

        $this->assertCount(1, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'inventory_id'
                ],
            ],
        ]);
    }
}
