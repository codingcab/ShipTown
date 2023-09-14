<?php

namespace App\Modules\InventoryTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class EnsureTotalsByWarehouseTagRecordsExistJob extends UniqueJob
{
    public function handle()
    {
        DB::statement("
            CREATE TEMPORARY TABLE tempTable AS
            SELECT
                taggables.tag_id, inventory.product_id
            FROM taggables

            INNER JOIN inventory
                ON inventory.warehouse_id = taggables.taggable_id

            LEFT JOIN inventory_totals_by_warehouse_tag
                ON inventory_totals_by_warehouse_tag.product_id = inventory.product_id
                AND inventory_totals_by_warehouse_tag.tag_id = taggables.tag_id

            WHERE
                taggables.taggable_type = 'App\\Models\\Warehouse'
                AND inventory_totals_by_warehouse_tag.id IS NULL

            LIMIT 10000;

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
                tempTable.tag_id as tag_id,
                tempTable.product_id as product_id,
                0 as quantity,
                0 as quantity_reserved,
                0 as quantity_available,
                0 as quantity_incoming,
                '2000-01-01 00:00:00' as max_inventory_updated_at,
                NOW() as created_at,
                NOW() as updated_at

            FROM tempTable
        ");
    }
}
