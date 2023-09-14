<?php

namespace App\Modules\InventoryTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class RecreateInventoryTotalsByWarehouseTagTableJob extends UniqueJob
{
    public function handle()
    {
        DB::statement("
            TRUNCATE TABLE inventory_totals_by_warehouse_tag;

            INSERT INTO inventory_totals_by_warehouse_tag (
                    tag_id,
                    product_id,
                    quantity,
                    quantity_reserved,
                    quantity_available,
                    quantity_incoming,
                    max_inventory_updated_at,
                    created_at,
                    updated_at
            )
            SELECT
                   taggables.tag_id as tag_id,
                   inventory.product_id as product_id,
                   GREATEST(0, FLOOR(SUM(inventory.quantity))) as quantity,
                   GREATEST(0, FLOOR(SUM(inventory.quantity_reserved))) as quantity_reserved,
                   GREATEST(0, FLOOR(SUM(inventory.quantity_available))) as quantity_available,
                   GREATEST(0, FLOOR(SUM(inventory.quantity_incoming))) as quantity_incoming,
                   MAX(inventory.updated_at) as max_inventory_updated_at,
                   NOW() as created_at,
                   NOW() as updated_at

            FROM inventory

            INNER JOIN taggables
                  ON taggables.taggable_type = 'App\\Models\\Warehouse'
                  AND taggables.taggable_id = inventory.warehouse_id

            GROUP BY taggables.tag_id, inventory.product_id
            ");
    }
}
