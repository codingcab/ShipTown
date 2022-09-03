<?php

namespace Tests\Feature\Http\Controllers\Api\DataCollectorRecordController;

use App\Models\DataCollection;
use App\Models\Product;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    /** @test */
    public function test_store_call_returns_ok()
    {
        $user = factory(User::class)->create();

        $dataCollection = factory(DataCollection::class)->create(['name' => 'test']);

        $response = $this->actingAs($user, 'api')->postJson(route('data-collector-records.store'), [
            'data_collection_id' => $dataCollection->getKey(),
            'product_id'=> factory(Product::class)->create()->getKey(),
            'quantity_scanned' => rand(0, 100),
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'product_id',
                'quantity_requested',
                'quantity_to_scan',
                'quantity_scanned',
            ],
        ]);
    }
}
