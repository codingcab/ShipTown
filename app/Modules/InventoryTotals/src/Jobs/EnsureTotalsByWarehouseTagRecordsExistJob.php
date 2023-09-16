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
        $this->batchSize = 1000;

        $this->config = Configuration::query()->firstOrCreate([]);

        $this->inventoryMaxId = Inventory::query()->max('id');
    }

    public function handle()
    {
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

            usleep(100000); // 0.1 sec
        } while ($maxID <= $this->inventoryMaxId);
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
                    taggables.tag_id, inventory.product_id

                FROM inventory

                INNER JOIN taggables
                  ON taggables.taggable_type = 'App\\\\Models\\\\Warehouse'
                  AND taggables.taggable_id = inventory.warehouse_id

                LEFT JOIN inventory_totals_by_warehouse_tag
                  ON inventory_totals_by_warehouse_tag.product_id = inventory.product_id
                  AND inventory_totals_by_warehouse_tag.tag_id = taggables.tag_id

                WHERE
                      inventory.id BETWEEN ? AND ?
                    AND inventory_totals_by_warehouse_tag.id is null
            ", [$minID, $maxID]);

        DB::insert("
                INSERT INTO inventory_totals_by_warehouse_tag (
                    tag_id,
                    product_id,
                    created_at,
                    updated_at
                )
                SELECT DISTINCT
                    tempTable.tag_id as tag_id,
                    tempTable.product_id as product_id,
                    NOW() as created_at,
                    NOW() as updated_at

                FROM tempTable
            ");
    }
}
