<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Modules\MagentoApi\src\Models\MagentoProduct;
use App\Modules\MagentoApi\src\Models\MagentoProductInventoryComparisonView;
use App\Modules\MagentoApi\src\Services\MagentoService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class SyncCheckFailedProductsJob.
 */
class FetchStockItemsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        MagentoProduct::query()
            ->whereRaw('IFNULL(exists_in_magento, 1) = 1')
            ->whereNull('stock_items_fetched_at')
            ->orWhereNull('quantity')
            ->with('magentoConnection')
            ->chunkById(10, function ($products) {
                collect($products)->each(function (MagentoProductInventoryComparisonView $product) {
                    try {
                        MagentoService::fetchInventory($product->magentoProduct);
                    } catch (Exception $exception) {
                        report($exception);
                    }
                });
            }, 'product_id');
    }
}
