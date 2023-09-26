<?php

namespace App\Modules\MagentoApi\src\Jobs\Sync;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use App\Modules\MagentoApi\src\Models\MagentoProductInventoryComparisonView;
use App\Modules\MagentoApi\src\Services\MagentoService;
use Illuminate\Support\Facades\Log;

class SyncProductInventoryJob extends UniqueJob
{
    public function handle()
    {
        $enabledConnections = MagentoConnection::query()
            ->where(['is_enabled' => true])
            ->whereNotNull('inventory_totals_tag_id')
            ->get();

        MagentoProduct::query()
            ->leftJoin('inventory_totals_by_warehouse_tag', 'inventory_totals_by_warehouse_tag.id', '=', 'modules_magento2api_products.inventory_totals_by_warehouse_tag_id')
            ->whereIn('connection_id', $enabledConnections->pluck('id'))
            ->whereNotNull('stock_items_fetched_at')
            ->whereNotNull('inventory_totals_by_warehouse_tag.id')
            ->whereRaw('(
                modules_magento2api_products.quantity IS NULL
                OR modules_magento2api_products.quantity != inventory_totals_by_warehouse_tag.quantity_available
            )')
            ->with(['magentoConnection', 'product', 'inventoryTotalsByWarehouseTag'])
            ->chunkById(10, function ($products) {
                collect($products)
                    ->each(function (MagentoProduct $comparison) {
                        ray($comparison);
                        $quantity = data_get($comparison, 'inventory_totals_by_warehouse_tag');
                        ray($quantity);
                        $response = MagentoService::updateInventory(
                            $comparison->magentoConnection,
                            $comparison->product->sku,
                            $comparison->inventoryTotalsByWarehouseTag->quantity_available
                        );

                        if ($response === null) {
                            Log::error('MAGENTO2API updateInventory returned null '.$comparison->magentoProduct->product->sku);
                            return;
                        }

                        $comparison->magentoProduct->update([
                            'stock_items_fetched_at' => null,
                            'stock_items_raw_import' => null,
                            'quantity'               => null,
                        ]);
                    });

                Log::debug('Job processing', [
                    'job' => self::class,
                    'products_updated' => $products->count(),
                ]);
            }, 'modules_magento2api_products.id');
    }
}
