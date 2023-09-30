<?php

namespace App\Modules\MagentoApi\src\Services;

use App\Modules\MagentoApi\src\Abstracts\EcommerceIntegration;
use App\Modules\MagentoApi\src\Api\Magento2Api;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class Magento2ApiIntegration extends EcommerceIntegration
{
    public static function fetchInventory(MagentoConnection $apiConnection, Collection $recordCollection): bool
    {
        $recordCollection->map(function (MagentoProduct $record) {
            $response = Magento2Api::api($record->magentoConnection)->getStockItems($record->product->sku);

            if ($response === null) {
                return false;
            }

            if ($response->notFound()) {
                $record->update(['exists_in_magento' => false]);
                return true;
            }

            if ($response->failed()) {
                throw new Exception('Failed to fetch stock items for product ' . $record->product->sku);
            }

            $record->update([
                'remote_id' => $response->json('item_id'),
                'quantity' => $response->json('qty'),
                'stock_items_fetched_at' => now(),
                'stock_items_raw_import' => $response->json(),
            ]);

            return true;
        });

        return true;
    }

    public static function updateInventory(MagentoConnection $apiConnection, Collection $recordCollection): bool
    {
        $recordCollection->map(function (MagentoProduct $record) use ($apiConnection) {
            $response = Magento2Api::api($apiConnection)
                ->putStockItems($record->product->sku, [
                    'is_in_stock' => $record->inventoryTotalsByWarehouseTag->quantity_available > 0,
                    'qty' => $record->inventoryTotalsByWarehouseTag->quantity_available,
                ]);

            if ($response === null) {
                return false;
            }

            if ($response->notFound()) {
                $record->update(['exists_in_magento' => false]);
                return false;
            }

            if ($response->failed()) {
                return false;
            }

            $record->update([
                'inventory_synced_at' => now(),
                'stock_items_fetched_at' => null,
            ]);

            return true;
        });

        return true;
    }

    public static function fetchBasePrices(Collection $recordCollection): bool
    {
        $recordCollection->map(function (MagentoProduct $record) {
            $response = Magento2Api::api($record->magentoConnection)
                ->postProductsBasePricesInformation($record->product->sku);

            if ($response === null) {
                return false;
            }

            if ($response->notFound()) {
                $record->update(['exists_in_magento' => false]);
                return false;
            }

            if ($response->failed()) {
                return false;
            }

            $record->update([
                'base_prices_fetched_at' => now(),
                'base_prices_raw_import' => $response->json(),
                'price' => data_get($response->json(), '0.price'),
            ]);

            return true;
        });

        return true;
    }

    /**
     * @throws Exception
     */
    public static function fetchSpecialPrices(Collection $recordCollection): bool
    {
        $recordCollection->map(function (MagentoProduct $record) {
            $response = Magento2Api::api($record->magentoConnection)->postProductsSpecialPriceInformation($record->product->sku);

            if ($response === null) {
                return false;
            }

            if ($response->notFound()) {
                $record->update([
                    'sale_price' => null,
                    'sale_price_start_date' => null,
                    'sale_price_end_date' => null,
                    'special_prices_fetched_at' => now(),
                    'special_prices_raw_import' => $response->json(),
                ]);
                return false;
            }

            if ($response->failed()) {
                throw new Exception('Failed to fetch sale prices for product ' . $record->product->sku);
            }
            $collect = collect($response->json());

            $specialPrices = $collect
                ->filter(function ($apiSpecialPriceRecord) use ($record) {
                    return $apiSpecialPriceRecord['store_id'] == $record->magentoConnection->magento_store_id ?? 0;
                });

            if ($specialPrices->isEmpty()) {
                $record->update([
                    'sale_price' => null,
                    'sale_price_start_date' => null,
                    'sale_price_end_date' => null,
                    'special_prices_fetched_at' => now(),
                    'special_prices_raw_import' => $response->json(),
                ]);
                return false;
            }

            // magento sometimes randomly returns multiple special prices for the same store,
            // so we need to filter them out but only one is valid
            // randomizing result will match it sometimes, normally 3 special prices are returned,
            // so we will have statistically 1/3 chance to get the correct one,
            // it's a quick hack, but it works
            $specialPrices = $specialPrices->shuffle();

            $specialPrice = $specialPrices->first();

            if ($specialPrice) {
                $record->update([
                    'sale_price' => $specialPrice['price'],
                    'sale_price_start_date' => $specialPrice['price_from'],
                    'sale_price_end_date' => $specialPrice['price_to'],
                ]);

                Log::debug('Magento2ApiIntegration::fetchSpecialPrices', [
                    'response' => $specialPrice,
                    'record' => $record->toArray(),
                ]);
            }

            $record->update([
                'special_prices_fetched_at' => now(),
                'special_prices_raw_import' => $response->json(),
            ]);

            return true;
        });

        return true;
    }

    public static function updateBasePrices(Collection $recordCollection): bool
    {
        $recordCollection->map(function (MagentoProduct $record) {
            $response = Magento2Api::api($record->magentoConnection)
                ->postProductsBasePrices(
                    $record->product->sku,
                    $record->prices->price,
                    $record->magentoConnection->magento_store_id ?? 0
                );

            if ($response === null) {
                return false;
            }

            $record->update([
                'price'                  => null,
                'base_prices_fetched_at' => null,
                'base_prices_raw_import' => null,
                'pricing_synced_at'      => now(),
            ]);

            return $response->successful();
        });

        return true;
    }

    public static function updateSalePrices(Collection $recordCollection): bool
    {
        $recordCollection->map(function (MagentoProduct $record) {
            $response = Magento2Api::api($record->magentoConnection)
                ->postProductsSpecialPrice(
                    $record->product->sku,
                    $record->magentoConnection->magento_store_id ?? 0,
                    $record->prices->sale_price,
                    $record->prices->sale_price_start_date->format('Y-m-d H:i:s'),
                    $record->prices->sale_price_end_date->format('Y-m-d H:i:s')
                );

            if ($response === null) {
                return false;
            }

            $record->update([
                'sale_prices_synced_at'     => now(),
                'special_prices_fetched_at' => null,
                'special_prices_raw_import' => null,
                'sale_price'                => null,
                'sale_price_start_date'     => null,
                'sale_price_end_date'       => null,
                'updated_at'                => now(),
            ]);

            return $response->successful();
        });

        return true;
    }
}
