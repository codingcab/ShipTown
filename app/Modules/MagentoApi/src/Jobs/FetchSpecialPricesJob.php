<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Modules\MagentoApi\src\Models\MagentoProduct;
use App\Modules\MagentoApi\src\Models\MagentoProductPricesComparisonView;
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
class FetchSpecialPricesJob implements ShouldQueue
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
            ->whereNull('special_prices_fetched_at')
            ->orWhereNull('magento_sale_price')
            ->chunkById(10, function ($products) {
                collect($products)->each(function (MagentoProductPricesComparisonView $product) {
                    try {
                        MagentoService::fetchSpecialPrices($product->magentoProduct);
                    } catch (Exception $exception) {
                        report($exception);
                    }
                });
            }, 'modules_magento2api_products_id');
    }
}
