<?php

namespace Tests\Feature\Modules\InventoryMovementsStatistics;

use App\Models\InventoryMovement;
use App\Modules\InventoryMovementsStatistics\src\Jobs\RepopulateStatisticsTableJob;
use Tests\TestCase;

class RepopulateStatisticsTableJobTest extends TestCase
{
    public function testBasic()
    {
        InventoryMovement::factory()->create(['type' => 'sale']);

        RepopulateStatisticsTableJob::dispatch();

        $this->assertDatabaseCount('inventory_movements_statistics', 1);
    }
}
