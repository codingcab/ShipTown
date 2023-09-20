<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use App\Modules\MagentoApi\src\Models\MagentoProductInventoryComparisonView;
use App\Modules\MagentoApi\src\Services\MagentoService;
use Exception;

class FetchSpecialPricesJob extends UniqueJob
{
    public function handle()
    {
        $connectionIds = MagentoConnection::query()->where(['is_enabled' => true])->get()->pluck('id');

        MagentoProduct::query()
            ->whereIn('magento_connection_id', $connectionIds)
            ->whereRaw('IFNULL(exists_in_magento, 1) = 1')
            ->whereNull('special_prices_fetched_at')
            ->orWhereNull('magento_sale_price')
            ->chunkById(10, function ($products) {
                collect($products)->each(function (MagentoProduct $magentoProduct) {
                    try {
                        MagentoService::fetchSpecialPrices($magentoProduct);
                    } catch (Exception $exception) {
                        report($exception);
                    }
                });
            }, 'product_id');
    }
}
