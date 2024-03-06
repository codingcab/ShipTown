<?php

namespace Tests\Unit\Modules\Inventory;

use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryReservations\src\Models\Configuration;
use Tests\TestCase;

class BasicModulesTest extends TestCase
{
    /** @test */
    public function basicTest()
    {
        $warehouse = Warehouse::factory()->create();

        $configuration = Configuration::firstOrCreate([]);
        $configuration->update(['warehouse_id' => $warehouse->getKey()]);

        $product = Product::factory()->create();

        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse->getKey(),
        ]);
    }
}
