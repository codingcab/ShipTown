<?php

namespace App\Modules\Magento2MSI\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Magento2MSI\src\Api\MagentoApi;
use App\Modules\Magento2MSI\src\Models\MagentoConnection;
use App\Modules\Magento2MSI\src\Models\MagentoProduct;
use Illuminate\Support\Collection;

class SyncProductInventoryJob extends UniqueJob
{
    public function handle(): void
    {
        MagentoConnection::query()
            ->get()
            ->each(function (MagentoConnection $magentoConnection) {
                MagentoProduct::query()
                    ->chunkById(10, function (Collection $chunk) use ($magentoConnection) {
                        $sourceItems = $chunk->map(function (MagentoProduct $magentoProduct) use ($magentoConnection) {
                            return [
                                'source_code' => $magentoConnection->magento_store_code,
                                'sku' => $magentoProduct->product->sku,
                                'quantity' => $magentoProduct->product->quantity,
                                'status' => 1,
                            ];
                        });

                        MagentoApi::postInventorySourceItems($magentoConnection->api_access_token, $sourceItems);
                    });
            });
    }
}
