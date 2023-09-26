<?php

namespace App\Modules\MagentoApi\src\Jobs\Maintenance;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class InvalidatePricingSyncedAtJob extends UniqueJob
{
    public function handle()
    {
        do {
            $recordsUpdated = $this->invalidateInventorySyncedAt(5000);
            usleep(100 * 1000); // 100ms
        } while ($recordsUpdated > 0);
    }

    private function invalidateInventorySyncedAt(int $limit = 1000): int
    {
        DB::statement('DROP TEMPORARY TABLE IF EXISTS tempTable');

        DB::statement("
            CREATE TEMPORARY TABLE tempTable AS
                SELECT
                    modules_magento2api_products.id as modules_magento2api_products_id
                FROM modules_magento2api_products

                INNER JOIN products_prices
                    ON products_prices.id = modules_magento2api_products.product_price_id

                WHERE modules_magento2api_products.pricing_synced_at IS NOT NULL
                    AND modules_magento2api_products.base_prices_fetched_at IS NOT NULL
                    AND (
                        modules_magento2api_products.price IS NULL
                        OR modules_magento2api_products.price != products_prices.price
                    )
                LIMIT ?;
        ", [$limit]);

        return DB::update("
            UPDATE modules_magento2api_products

            INNER JOIN tempTable
                ON tempTable.modules_magento2api_products_id = modules_magento2api_products.id

            SET
                modules_magento2api_products.pricing_synced_at = NULL
        ");
    }
}
