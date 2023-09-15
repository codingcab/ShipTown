<?php

namespace Tests\Feature\Modules\InventoryTotals;

use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use Spatie\Tags\Tag;
use Tests\ResetsDatabase;
use Tests\TestCase;

class TotalsByWarehouseTagTest extends TestCase
{
    use ResetsDatabase;

    /** @test */
    public function test_module_basic_functionality()
    {
        InventoryTotalsServiceProvider::enableModule();

        $warehouse = Warehouse::factory()->create();
        $warehouse->attachTag('test_tag');

        $product = Product::factory()->create();

        ray('warehouses', Warehouse::query()->get()->toArray());
        ray('products', Product::query()->get()->toArray());

        $this->assertDatabaseHas('inventory_totals_by_warehouse_tag', [
            'tag_id' => Tag::findFromString('test_tag')->getKey(),
            'product_id' => $product->getKey(),
            'quantity' => 0
        ]);
    }
}
