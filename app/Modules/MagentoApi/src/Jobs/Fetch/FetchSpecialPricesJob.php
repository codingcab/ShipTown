<?php

namespace App\Modules\MagentoApi\src\Jobs\Fetch;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FetchSpecialPricesJob extends UniqueJob
{
    public function handle()
    {
        MagentoConnection::query()
            ->where(['is_enabled' => true])
            ->whereNotNull('pricing_source_warehouse_id')
            ->get()
            ->each(function (MagentoConnection $magentoConnection) {
                MagentoProduct::query()
                    ->where(['exists_in_magento' => true])
                    ->where(['connection_id' => $magentoConnection->getKey()])
                    ->whereNull('special_prices_fetched_at')
                    ->with('magentoConnection', 'product', 'prices')
                    ->chunkById(10, function (Collection $products) use ($magentoConnection) {
                        $magentoConnection->service_class::fetchSpecialPrices($products);

                        Log::debug('Job processing', [
                            'job' => self::class,
                            'products_fetched' => $products->count(),
                        ]);
                    });
            });
    }
}
