<?php

namespace App\Modules\InventoryTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Inventory;
use App\Modules\InventoryTotals\src\Models\Configuration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnsureTotalsRecordsExistJob extends UniqueJob
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
            $minID = $maxID ?? $this->config->totals_max_product_id_checked;
            $maxID = $minID + $this->batchSize;

            $this->insertMissingRecords($minID, $maxID);

            Log::debug('Processing job', [
                'job' => self::class,
                'records created' => DB::table('tempTable')->count(),
                'minID' => $minID,
                'maxID' => $maxID,
            ]);

            $this->config->update(['totals_max_inventory_id_checked' => $maxID]);

            usleep(100000); // 0.1 sec
        } while ($maxID <= $this->inventoryMaxId);
    }

    public function fail($exception = null)
    {
        Log::error('Job failed', ['job' => self::class, 'error' => $exception->getMessage()]);
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
                    products.id

                FROM products

                LEFT JOIN inventory_totals
                  ON inventory_totals.product_id = products.id

                WHERE
                    products.id BETWEEN ? AND ?
                    AND inventory_totals.id is null
            ", [$minID, $maxID]);

        DB::insert("
                INSERT INTO inventory_totals (
                    product_id,
                    created_at,
                    updated_at
                )
                SELECT
                    tempTable.product_id as product_id,
                    NOW() as created_at,
                    NOW() as updated_at

                FROM tempTable
            ");
    }
}
