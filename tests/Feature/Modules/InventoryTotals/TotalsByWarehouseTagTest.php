<?php

namespace Tests\Feature\Modules\InventoryTotals;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use App\Modules\InventoryTotals\src\Models\InventoryTotalByWarehouseTag;
use App\Services\InventoryService;
use Spatie\Tags\Tag;
use Tests\ResetsDatabase;
use Tests\TestCase;

class TotalsByWarehouseTagTest extends TestCase
{
    use ResetsDatabase;

    /** @test */
    public function test_basic_scenario()
    {
        InventoryTotalsServiceProvider::enableModule();

        $warehouse1_withTag = Warehouse::factory()->create();
        $warehouse1_withTag->attachTag('test_tag');

        $warehouse2_withTag = Warehouse::factory()->create();
        $warehouse2_withTag->attachTag('test_tag');

        $warehouse3_noTag = Warehouse::factory()->create();

        $product = Product::factory()->create();

        $inventory1_withTag = Inventory::query()->where([
                'product_id' => $product->getKey(),
                'warehouse_id' => $warehouse1_withTag->getKey()
            ])->first();

        $inventory2_withTag = Inventory::query()->where([
                'product_id' => $product->getKey(),
                'warehouse_id' => $warehouse2_withTag->getKey()
            ])->first();

        $inventory3_noTag = Inventory::query()->where([
                'product_id' => $product->getKey(),
                'warehouse_id' => $warehouse3_noTag->getKey()
            ])->first();

        InventoryService::adjustQuantity($inventory1_withTag, 10, 'test');
        InventoryService::adjustQuantity($inventory2_withTag, 5, 'test');
        InventoryService::adjustQuantity($inventory3_noTag, 3, 'test');

        ray('inventory_totals_by_warehouse_tag', InventoryTotalByWarehouseTag::query()->first()->toArray());

        $this->assertDatabaseHas('inventory_totals_by_warehouse_tag', [
            'tag_id' => Tag::findFromString('test_tag')->getKey(),
            'product_id' => $product->getKey(),
            'quantity' => 15
        ]);
    }

    /** @test */
    public function test_module_basic_functionality()
    {
        InventoryTotalsServiceProvider::enableModule();

        $warehouse = Warehouse::factory()->create();
        $warehouse->attachTag('test_tag');

        $product = Product::factory()->create();

        ray('warehouses', Warehouse::query()->get()->toArray());
        ray('products', Product::query()->get()->toArray());
        ray('inventory_totals_by_warehouse_tag', InventoryTotalByWarehouseTag::query()->first()->toArray());

        $this->assertDatabaseHas('inventory_totals_by_warehouse_tag', [
            'tag_id' => Tag::findFromString('test_tag')->getKey(),
            'product_id' => $product->getKey(),
            'quantity' => 0
        ]);
    }
}
