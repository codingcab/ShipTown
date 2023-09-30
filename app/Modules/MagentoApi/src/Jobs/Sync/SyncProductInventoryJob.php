<?php

namespace App\Modules\MagentoApi\src\Jobs\Sync;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use Illuminate\Support\Facades\Log;

class SyncProductInventoryJob extends UniqueJob
{
    public function handle()
    {
        MagentoConnection::query()
            ->where(['is_enabled' => true])
            ->whereNotNull('inventory_totals_tag_id')
            ->get()
            ->each(function (MagentoConnection $magentoConnection) {
                MagentoProduct::query()
                    ->where(['exists_in_magento' => true])
                    ->where(['connection_id' => $magentoConnection->getKey()])
                    ->whereNull('inventory_synced_at')
                    ->with(['magentoConnection', 'product', 'inventoryTotalsByWarehouseTag'])
                    ->chunkById(10, function ($products) use ($magentoConnection) {
                        $magentoConnection->service_class::updateInventory($magentoConnection, $products);

                        MagentoProduct::query()->whereIn('id', $products->pluck('id'))->update([
                            'inventory_synced_at' => now(),
                        ]);

                        Log::debug('Job processing', [
                            'job' => self::class,
                            'products_updated' => $products->count(),
                        ]);
                    });
            });
    }
}
