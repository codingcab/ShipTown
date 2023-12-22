<?php

namespace App\Modules\Integrations\Magento2MSI\src\Jobs;

use App\Modules\Integrations\Magento2MSI\src\Models\MagentoProduct;
use App\Modules\Integrations\Magento2MSI\src\Services\MagentoService;
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
            ->orWhereNull('special_prices_raw_import')
            ->chunkById(100, function ($products) {
                collect($products)->each(function (MagentoProduct $product) {
                    try {
                        MagentoService::fetchSpecialPrices($product);
                    } catch (Exception $exception) {
                        report($exception);
                    }
                });
            });
    }
}
