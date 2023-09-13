<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoProductInventoryComparisonView;
use App\Modules\MagentoApi\src\Services\MagentoService;

class SyncProductInventoryJob extends UniqueJob
{
    public function handle()
    {
        MagentoProductInventoryComparisonView::query()
            ->whereNotNull('stock_items_fetched_at')
            ->whereRaw('IFNULL(magento_quantity, 0) != expected_quantity')
            ->with('magentoConnection')
            ->chunkById(100, function ($products) {
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
            }, 'modules_magento2api_products_id');
    }
}
