<?php

namespace App\Modules\InventoryMovementsStatistics\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Inventory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecalculateStatisticsTableJob extends UniqueJob
{
    public function handle($soldSince = null): void
    {
        $lastSoldAt = $soldSince ?: now()->subDays(60);

        Inventory::query()
            ->where('last_sold_at', '>=', $lastSoldAt)
            ->chunkById(25, function (Collection $inventories) {
                $this->recalculateInventoryStatistics($inventories->pluck('id'));
                Log::info('Job processing', [
                    'job' => self::class,
                    'records_updated' => $inventories->count()
                ]);
                usleep(100000); // 0.1 second
            });
    }

    public function recalculateInventoryStatistics(Collection $inventory): void
    {
        DB::statement('
            REPLACE INTO inventory_movements_statistics (
                type,
                inventory_id,
                product_id,
                warehouse_code,
                last7days_quantity_delta,
                last7days_max_movement_id,
                last7days_min_movement_id,
                last14days_quantity_delta,
                last14days_max_movement_id,
                last14days_min_movement_id,
                last28days_quantity_delta,
                last28days_max_movement_id,
                last28days_min_movement_id,
                created_at,
                updated_at
            )
            SELECT
                inventory_movements.type,
                inventory_movements.inventory_id,
                max(inventory_movements.product_id) as product_id,
                max(warehouses.code) as warehouse_code,
                sum(CASE WHEN inventory_movements.occurred_at > date_sub(now(), interval 7 day) THEN inventory_movements.quantity_delta ELSE 0 END) as last7days_quantity_delta,
                max(CASE WHEN inventory_movements.occurred_at > date_sub(now(), interval 7 day) THEN inventory_movements.id ELSE NULL END) as last7days_max_movement_id,
                min(CASE WHEN inventory_movements.occurred_at > date_sub(now(), interval 7 day) THEN inventory_movements.id ELSE NULL END) as last7days_min_movement_id,
                sum(CASE WHEN inventory_movements.occurred_at > date_sub(now(), interval 14 day) THEN inventory_movements.quantity_delta ELSE 0 END) as last14days_quantity_delta,
                max(CASE WHEN inventory_movements.occurred_at > date_sub(now(), interval 14 day) THEN inventory_movements.id ELSE NULL END) as last14days_max_movement_id,
                min(CASE WHEN inventory_movements.occurred_at > date_sub(now(), interval 14 day) THEN inventory_movements.id ELSE NULL END) as last14days_min_movement_id,
                sum(CASE WHEN inventory_movements.occurred_at > date_sub(now(), interval 28 day) THEN inventory_movements.quantity_delta ELSE 0 END) as last28days_quantity_delta,
                max(CASE WHEN inventory_movements.occurred_at > date_sub(now(), interval 28 day) THEN inventory_movements.id ELSE NULL END) as last28days_max_movement_id,
                min(CASE WHEN inventory_movements.occurred_at > date_sub(now(), interval 28 day) THEN inventory_movements.id ELSE NULL END) as last28days_min_movement_id,
                now() as created_at,
                now() as updated_at
            FROM inventory_movements
            LEFT JOIN warehouses ON warehouses.id = inventory_movements.warehouse_id
            WHERE inventory_movements.inventory_id IN ('. $inventory->implode(',') .')
            GROUP BY inventory_movements.type, inventory_movements.inventory_id
        ');
    }
}
