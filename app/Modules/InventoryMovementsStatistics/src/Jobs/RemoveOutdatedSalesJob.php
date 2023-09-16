<?php

namespace App\Modules\InventoryMovementsStatistics\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class RemoveOutdatedSalesJob extends UniqueJob
{
    public function handle()
    {
        $this->removeOutdatedSales(7);
        $this->removeOutdatedSales(14);
        $this->removeOutdatedSales(28);
    }

    private function removeOutdatedSales($days): void
    {
        collect(DB::select('
            WITH tempTable AS (
                SELECT
                  inventory_movements.type,
                  inventory_movements.inventory_id,
                  min(inventory_movements_statistics.last'.$days.'days_min_movement_id) as current_first_movement_id,
                  min(inventory_movements.id) as new_first_movement_id,
                  sum(inventory_movements.quantity_delta) as sum_quantity_delta
                FROM inventory_movements_statistics
                INNER JOIN inventory_movements
                 ON inventory_movements.inventory_id = inventory_movements_statistics.inventory_id
                 AND inventory_movements.type = inventory_movements_statistics.type
                 AND inventory_movements.id >= inventory_movements_statistics.last'.$days.'days_min_movement_id
                 AND inventory_movements.created_at < date_sub(now(), interval '.$days.' day)
                WHERE inventory_movements_statistics.last'.$days.'days_min_movement_id IS NOT NULL
                GROUP BY inventory_movements.type, inventory_movements.inventory_id
                LIMIT 1000
            )

            UPDATE inventory_movements_statistics
            INNER JOIN tempTable
                ON tempTable.inventory_id = inventory_movements_statistics.inventory_id
                AND tempTable.type = inventory_movements_statistics.type
            SET
              last7days_min_movement_id = (
                    SELECT min(id)
                    FROM inventory_movements
                    WHERE inventory_movements.inventory_id = tempTable.inventory_id
                    AND inventory_movements.type = tempTable.type
                    AND inventory_movements.id > inventory_movements_statistics.last'.$days.'days_min_movement_id
                ),
              last'.$days.'days_quantity_delta = last'.$days.'days_quantity_delta - sum_quantity_delta
        '));
    }
}
