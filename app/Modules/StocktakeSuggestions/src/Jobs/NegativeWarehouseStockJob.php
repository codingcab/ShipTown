<?php

namespace App\Modules\StocktakeSuggestions\src\Jobs;

use App\Models\Warehouse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class NegativeWarehouseStockJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use IsMonitored;

    private int $warehouse_id;

    public function __construct(int $warehouse_id)
    {
        $this->warehouse_id = $warehouse_id;
    }

    public function handle(): bool
    {
        $reason = 'negative warehouse stock';
        $points = 20;

        $warehouseIDs = Warehouse::withAllTags(['fulfilment'])->pluck('id');

        if ($warehouseIDs->isEmpty()) {
            return true;
        }

        DB::statement("
            DELETE FROM stocktake_suggestions
            WHERE warehouse_id IN (?)
            AND reason = ?
        ", [$warehouseIDs->implode(','), $reason]);

        DB::statement('
            INSERT INTO stocktake_suggestions (inventory_id, product_id, warehouse_id, points, reason, created_at, updated_at)
                SELECT DISTINCT inventory.id as inventory_id,
                    inventory.product_id as product_id,
                    inventory.warehouse_id as warehouse_id,
                    20 + ABS(inventory.quantity) as points,
                    ? as reason,
                    now() as created_at,
                    now() as updated_at
                FROM inventory
                RIGHT JOIN inventory as inventory_fullfilment
                    ON inventory_fullfilment.product_id = inventory.product_id
                    AND inventory_fullfilment.warehouse_id IN (' . implode(',', $warehouseIDs->toArray()) . ')
                    AND inventory_fullfilment.quantity > 0
                WHERE inventory.warehouse_id = ?
                    AND inventory.quantity < 0
                    AND NOT EXISTS (
                        SELECT NULL
                        FROM stocktake_suggestions
                        WHERE stocktake_suggestions.inventory_id = inventory.id
                        AND stocktake_suggestions.reason = ?
                    )
                LIMIT 500
        ', [$points, $reason, $this->warehouse_id, $reason]);

        return true;
    }
}
