<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProductInventoryComparisonView;
use App\Modules\MagentoApi\src\Services\MagentoService;
use Illuminate\Support\Facades\Log;

class SyncProductInventoryJob extends UniqueJob
{
    public function handle()
    {
        $enabledConnections = MagentoConnection::query()
            ->where(['is_enabled' => true])
            ->whereNotNull('inventory_source_warehouse_tag_id')
            ->get();

        MagentoProductInventoryComparisonView::query()
            ->whereIn('modules_magento2api_connection_id', $enabledConnections->pluck('id'))
            ->whereNotNull('stock_items_fetched_at')
            ->whereRaw('(
                magento_quantity IS NULL
                OR magento_quantity != expected_quantity
            )')
            ->with('magentoConnection')
            ->chunkById(10, function ($products) {
                collect($products)->each(function (MagentoProductInventoryComparisonView $comparison) {
                    MagentoService::updateInventory(
                        $comparison->magentoConnection,
                        $comparison->magentoProduct->product->sku,
                        $comparison->expected_quantity
                    );

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
            }, 'modules_magento2api_products_id');
    }
}
