<?php

namespace App\Modules\InventoryTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Inventory;
use App\Modules\InventoryTotals\src\Models\Configuration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnsureTotalsByWarehouseTagRecordsExistJob extends UniqueJob
{
    private int $batchSize;
    private Configuration|Model $config;
    private mixed $inventoryMaxId;

    public function __construct()
    {
        $this->batchSize = 10000;

        $this->config = Configuration::query()->firstOrCreate([]);

        $this->inventoryMaxId = Inventory::query()->max('id');
    }

    public function handle()
    {
        Log::debug('Starting job', ['job' => self::class]);

        do {
            $minID = $maxID ?? $this->config->totals_by_warehouse_tag_max_inventory_id_checked;
            $maxID = $minID + $this->batchSize;

            $this->insertMissingRecords($minID, $maxID);

            Log::debug('Processing job', [
                'job' => self::class,
                'records created' => DB::table('tempTable')->count(),
                'maxID' => $maxID,
                'minID' => $minID,
            ]);

            $this->config->update(['totals_by_warehouse_tag_max_inventory_id_checked' => $maxID]);

            sleep(1);
        } while ($maxID <= $this->inventoryMaxId);

        Log::debug('Finished job', ['job' => self::class]);
    }

    public function fail($exception = null)
    {
        Log::error('EnsureTotalsByWarehouseTagRecordsExistJob', ['error' => $exception->getMessage()]);
        report($exception);
    }

    /**
     * @param mixed $minID
     * @param mixed $maxID
     */
    private function insertMissingRecords(mixed $minID, mixed $maxID): void
    {
        DB::statement("DROP TEMPORARY TABLE IF EXISTS tempTable;");

        DB::statement("
                CREATE TEMPORARY TABLE tempTable AS
                SELECT
                 DISTINCT taggables.tag_id, inventory.product_id, taggables.taggable_id as warehouse_id

                FROM inventory

                INNER JOIN taggables
                  ON taggables.taggable_type = 'App\\\\Models\\\\Warehouse'
                  AND taggables.taggable_id = inventory.warehouse_id

                LEFT JOIN inventory_totals_by_warehouse_tag
                  ON inventory_totals_by_warehouse_tag.tag_id = taggables.tag_id
                  AND inventory_totals_by_warehouse_tag.product_id = inventory.product_id

                WHERE inventory.id BETWEEN ? AND ?
                AND inventory_totals_by_warehouse_tag.id is null
            ", [$minID, $maxID]);

        DB::insert("
                INSERT INTO inventory_totals_by_warehouse_tag (
                    tag_id,
                    product_id,
                    quantity,
                    quantity_reserved,
                    quantity_available,
                    quantity_incoming,
                    max_inventory_updated_at,
                    calculated_at,
                    created_at,
                    updated_at
                )
                SELECT
                    tempTable.tag_id as tag_id,
                    tempTable.product_id as product_id,
                    SUM(inventory.quantity) as quantity,
                    SUM(inventory.quantity_reserved) as quantity_reserved,
                    SUM(inventory.quantity_available) as quantity_available,
                    SUM(inventory.quantity_incoming) as quantity_incoming,
                    MAX(inventory.updated_at) as max_inventory_updated_at,
                    NOW() as calculated_at,
                    NOW() as created_at,
                    NOW() as updated_at

                FROM tempTable
                INNER JOIN inventory
                    ON inventory.product_id = tempTable.product_id
                    AND inventory.warehouse_id = tempTable.warehouse_id

                GROUP BY tempTable.tag_id, tempTable.product_id;
            ");
    }
}
