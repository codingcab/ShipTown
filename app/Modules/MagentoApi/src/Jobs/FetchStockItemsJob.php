<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use App\Modules\MagentoApi\src\Services\MagentoService;
use Illuminate\Support\Facades\Log;

class FetchStockItemsJob extends UniqueJob
{
    public function handle()
    {
        $connectionIds = MagentoConnection::query()->where(['is_enabled' => true])->get()->pluck('id');

        MagentoProduct::query()
            ->whereIn('connection_id', $connectionIds)
            ->whereRaw('IFNULL(exists_in_magento, 1) = 1')
            ->whereRaw('stock_items_fetched_at IS NULL OR quantity IS NULL')
            ->where(['product_id' => 406430])
            ->with('magentoConnection')
            ->chunkById(10, function ($products) {
                collect($products)->each(function (MagentoProduct $magentoProduct) {
                    MagentoService::fetchInventory($magentoProduct);
                });
                Log::debug('Job processing', [
                    'job' => self::class,
                    'products_fetched' => $products->count(),
                ]);
            }, 'product_id');
    }

    public function fail($exception = null)
    {
        report($exception);
    }
}
