<?php

namespace Tests\Unit\Modules\InventoryMovementsStatistics;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;
use App\Modules\InventoryMovementsStatistics\src\Jobs\RecalculateStatisticsTableJob;
use App\Modules\InventoryTotals\src\Jobs\RecalculateInventoryRecordsJob;
use Tests\TestCase;

class RepopulateStatisticsTableJobTest extends TestCase
{
    public function testBasic()
    {
        $product1 = Product::factory()->create();

        InventoryMovement::factory()->create(['product_id' => $product1->getKey(), 'type' => 'sale', 'occurred_at' => now()->subDays(2)]);
        InventoryMovement::factory()->create(['product_id' => $product1->getKey(), 'type' => 'sale', 'occurred_at' => now()->subDays(2)]);
        InventoryMovement::factory()->create(['product_id' => $product1->getKey(), 'type' => 'sale', 'occurred_at' => now()->subDays(2)]);

        $product2 = Product::factory()->create();
        InventoryMovement::factory()->create(['product_id' => $product2->getKey(), 'type' => 'sale', 'occurred_at' => now()->subDays(2)]);
        InventoryMovement::factory()->create(['product_id' => $product2->getKey(), 'type' => 'sale', 'occurred_at' => now()->subDays(2)]);
        InventoryMovement::factory()->create(['product_id' => $product2->getKey(), 'type' => 'sale', 'occurred_at' => now()->subDays(2)]);

        SequenceNumberJob::dispatchSync();

        RecalculateInventoryRecordsJob::dispatchSync();

        RecalculateStatisticsTableJob::dispatch();

        $this->assertDatabaseCount('inventory_movements_statistics', 2);
    }
}
