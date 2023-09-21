<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use App\Modules\MagentoApi\src\Services\MagentoService;

class FetchBasePricesJob extends UniqueJob
{
    public function handle()
    {
        $connectionIds = MagentoConnection::query()->where(['is_enabled' => true])->get()->pluck('id');

        MagentoProduct::query()
            ->whereIn('connection_id', $connectionIds)
            ->whereRaw('IFNULL(exists_in_magento, 1) = 1')
            ->whereRaw('base_prices_fetched_at IS NULL OR magento_price IS NULL')
            ->where(['product_id' => 406430])
            ->chunkById(10, function ($products) {
                collect($products)->each(function (MagentoProduct $magentoProduct) {
                    MagentoService::fetchBasePrices($magentoProduct);
                });
            }, 'product_id');
    }

    public function fail($exception = null)
    {
        report($exception);
    }
}
