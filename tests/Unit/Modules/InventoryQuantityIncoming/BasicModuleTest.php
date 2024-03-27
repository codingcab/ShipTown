<?php

namespace Tests\Unit\Modules\InventoryQuantityIncoming;

use App\Models\DataCollection;
use App\Models\DataCollectionTransferIn;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryQuantityIncoming\src\InventoryQuantityIncomingServiceProvider;
use App\Modules\InventoryQuantityIncoming\src\Jobs\FixIncorrectQuantityIncomingJob;
use App\Modules\InventoryQuantityIncoming\src\Jobs\RecalculateInventoryQuantityIncomingJob;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    public function test_basic_functionality()
    {
        InventoryQuantityIncomingServiceProvider::enableModule();

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $inventory = Inventory::query()->where([
            'warehouse_id' => $warehouse->getKey(),
            'product_id' => $product->getKey(),
        ])->first();

        $inventory->update([
            'quantity' => 10,
            'restock_level' => 0,
            'reorder_point' => 0,
        ]);

        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::factory()->create([
            'type' => DataCollectionTransferIn::class,
            'name' => 'Test',
            'warehouse_id' => $warehouse->getKey(),
        ]);

        $dataCollection->records()->create([
            'inventory_id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'quantity_requested' => 10,
        ]);

        $this->assertEquals(10, $inventory->fresh()->quantity_incoming);
    }

    public function test_FixIncorrectQuantityIncomingJob()
    {
        InventoryQuantityIncomingServiceProvider::enableModule();

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $inventory = Inventory::query()->where([
            'warehouse_id' => $warehouse->getKey(),
            'product_id' => $product->getKey(),
        ])->first();

        $inventory->update([
            'quantity' => 10,
            'restock_level' => 0,
            'reorder_point' => 0,
        ]);

        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::factory()->create([
            'type' => DataCollectionTransferIn::class,
            'name' => 'Test',
            'warehouse_id' => $warehouse->getKey(),
        ]);

        $record = $dataCollection->records()->create([
            'inventory_id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'quantity_requested' => 10,
        ]);

        $inventory->update(['quantity_incoming' => 11]);

        FixIncorrectQuantityIncomingJob::dispatch();

        $this->assertEquals(10, $inventory->fresh()->quantity_incoming);
    }

    public function test_if_zeroed_when_incorrectly_assigned()
    {
        InventoryQuantityIncomingServiceProvider::enableModule();

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $inventory = Inventory::query()->where([
            'warehouse_id' => $warehouse->getKey(),
            'product_id' => $product->getKey(),
        ])->first();

        $inventory->update([
            'quantity' => 10,
            'restock_level' => 0,
            'reorder_point' => 0,
        ]);

        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::factory()->create([
            'type' => DataCollectionTransferIn::class,
            'name' => 'Test',
            'warehouse_id' => $warehouse->getKey(),
        ]);

        $inventory->update(['quantity_incoming' => 11]);

        FixIncorrectQuantityIncomingJob::dispatch();

        $this->assertEquals(0, $inventory->fresh()->quantity_incoming);
    }

    public function test_RecalculateInventoryQuantityIncomingJob()
    {
        InventoryQuantityIncomingServiceProvider::enableModule();

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $inventory = Inventory::query()->where([
            'warehouse_id' => $warehouse->getKey(),
            'product_id' => $product->getKey(),
        ])->first();

        $inventory->update([
            'quantity' => 10,
            'restock_level' => 0,
            'reorder_point' => 0,
        ]);

        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::factory()->create([
            'type' => DataCollectionTransferIn::class,
            'name' => 'Test',
            'warehouse_id' => $warehouse->getKey(),
        ]);

        $dataCollection->records()->create([
            'inventory_id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'quantity_requested' => 10,
        ]);

        $inventory->update(['quantity_incoming' => 11]);

        RecalculateInventoryQuantityIncomingJob::dispatch(
            $inventory->product_id,
            $inventory->warehouse_id
        );

        $this->assertEquals(10, $inventory->fresh()->quantity_incoming);
    }
}
