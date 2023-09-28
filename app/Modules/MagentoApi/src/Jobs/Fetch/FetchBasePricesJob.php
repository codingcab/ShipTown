<?php

namespace App\Modules\MagentoApi\src\Jobs\Fetch;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use App\Modules\MagentoApi\src\Services\MagentoService;
use Illuminate\Support\Facades\Log;

class FetchBasePricesJob extends UniqueJob
{
    public function handle()
    {
        $connectionIds = MagentoConnection::query()
            ->where(['is_enabled' => true])
            ->whereNotNull('pricing_source_warehouse_id')
            ->get();

        MagentoProduct::query()
            ->where(['exists_in_magento' => true])
            ->whereIn('connection_id', $connectionIds->pluck('id'))
            ->whereNull('base_prices_fetched_at')
            ->with('magentoConnection', 'product', 'prices')
            ->chunkById(10, function ($products) {
                collect($products)->each(function (MagentoProduct $magentoProduct) {
                    MagentoService::fetchBasePrices($magentoProduct);
                });

                Log::debug('Job processing', [
                    'job' => self::class,
                    'products_fetched' => $products->count(),
                ]);
            }, 'product_id');
    }
}
