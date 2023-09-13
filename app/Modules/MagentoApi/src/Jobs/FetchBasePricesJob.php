<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use App\Modules\MagentoApi\src\Services\MagentoService;
use Exception;

class FetchBasePricesJob extends UniqueJob
{
    public function handle()
    {
        MagentoProduct::query()
            ->whereRaw('IFNULL(exists_in_magento, 1) = 1')
            ->whereNull('base_prices_fetched_at')
            ->orWhereNull('magento_price')
            ->chunkById(10, function ($products) {
                collect($products)->each(function (MagentoProduct $magentoProduct) {
                    try {
                        MagentoService::fetchBasePrices($magentoProduct);
                    } catch (Exception $exception) {
                        report($exception);
                    }
                });
            }, 'product_id');
    }
}
