<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Modules\MagentoApi\src\Models\MagentoProductPricesComparisonView;
use App\Modules\MagentoApi\src\Services\MagentoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class SyncCheckFailedProductsJob.
 */
class SyncProductBasePricesJob implements ShouldQueue
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
        MagentoProductPricesComparisonView::query()
            ->whereNotNull('base_prices_fetched_at')
            ->whereRaw('IFNULL(magento_price, 0) != expected_price')
            ->chunkById(100, function ($products) {
                collect($products)->each(function (MagentoProductPricesComparisonView $comparison) {
                    MagentoService::updateBasePrice(
                        $comparison->magentoConnection,
                        $comparison->sku,
                        $comparison->expected_price,
                        $comparison->magento_store_id
                    );

                    $comparison->magentoProduct->update([
                        'base_prices_fetched_at' => null,
                        'base_prices_raw_import' => null,
                        'magento_price'          => null,
                    ]);
                });
            }, 'modules_magento2api_products_id');
    }
}
