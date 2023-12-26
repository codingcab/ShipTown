<?php

namespace Tests\Feature\Csv;

use App\User;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Csv\ProductsPickedInWarehouse
 */
class ProductsPickedInWarehouseTest extends TestCase
{
    /**
     * @test
     */
    public function index_returns_an_ok_response()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('warehouse_picks.csv'));

        $response->assertOk();
    }
}
