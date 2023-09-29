<?php

namespace App\Modules\MagentoApi\src\Services;

use App\Modules\MagentoApi\src\Abstracts\EcommerceIntegration;
use App\Modules\MagentoApi\src\Api\Magento2Api;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class Magento2ApiIntegrationBck extends EcommerceIntegration
{
    public static function api(MagentoConnection $magentoConnection = null): Magento2Api
    {
        /** @var MagentoConnection $magentoConnection */
        $magentoConnection = $magentoConnection ?? MagentoConnection::first();
        return new Magento2Api($magentoConnection);
    }

    public static function updateBasePrice(MagentoConnection $magentoConnection, string $sku, float $price, int $store_id)
    {
        self::api($magentoConnection)
            ->postProductsBasePrices(
                $sku,
                $price,
                $store_id
            );
    }

    public static function updateSalePrice(MagentoConnection $magentoConnection, string $sku, float $sale_price, $start_date, $end_date, int $store_id)
    {
        $response = self::api($magentoConnection)
            ->postProductsSpecialPrice(
                $sku,
                $store_id,
                $sale_price,
                $start_date,
                $end_date
            );

        if (! $response->successful()) {
            Log::error('Failed to fetch sale prices for product '.$sku);
        }
    }

    /**
     * @throws Exception
     */
    public static function fetchSpecialPrices(MagentoProduct $record)
    {
        $response = self::api($record->magentoConnection)
            ->postProductsSpecialPriceInformation($record->product->sku);

        if ($response === null) {
            throw new Exception('MAGENTO2API fetchSpecialPrices returned null '.$record->product->sku);
        }

        if ($response->notFound()) {
            $record->update(['exists_in_magento' => false]);
            return;
        }

        if ($response->failed()) {
            throw new Exception('Failed to fetch sale prices for product '.$record->product->sku);
        }

        $collect = collect($response->json());

        $specialPrices = $collect
            ->filter(function ($apiSpecialPriceRecord) use ($record) {
                return $apiSpecialPriceRecord['store_id'] == $record->magentoConnection->magento_store_id;
            });


        // magento sometimes randomly returns multiple special prices for the same store,
        // so we need to filter them out but only one is valid
        // randomizing result will match it sometimes, normally 3 special prices are returned,
        // so we will have statistically 1/3 chance to get the correct one,
        // it's a quick hack, but it works
        $specialPrices = $specialPrices->shuffle();

        $specialPrice = $specialPrices->first();

        Log::debug('Fetched special prices for product '.$record->product->sku, [
            'special_prices' => $specialPrices->toArray(),
            'special_price' => $specialPrice,
        ]);

        if ($specialPrice) {
            $record->sale_price = $specialPrice['price'];
            $record->magento_sale_price_start_date = $specialPrice['price_from'];
            $record->magento_sale_price_end_date = $specialPrice['price_to'];
        }

        $record->special_prices_fetched_at = now();
        $record->special_prices_raw_import = $response->json();
        $record->save();
    }

    /**
     * @throws Exception
     */
    public static function fetchBasePrices(MagentoProduct $magentoProduct)
    {
        $response = self::api($magentoProduct->magentoConnection)
            ->postProductsBasePricesInformation($magentoProduct->product->sku);

        if ($response === null) {
            throw new Exception('MAGENTO2API fetchBasePrices call returned null');
        }

        if ($response->notFound()) {
            $magentoProduct->update(['exists_in_magento' => false]);
            return;
        }

        if ($response->failed()) {
            throw new Exception('Failed to fetch base prices for product '.$magentoProduct->product->sku);
        }

        $magentoProduct->base_prices_fetched_at = now();
        $magentoProduct->base_prices_raw_import = $response->json();

        collect($response->json())
            ->filter(function ($item) use ($magentoProduct) {
                return $item['store_id'] === $magentoProduct->magentoConnection->magento_store_id;
            })
            ->each(function ($item) use ($magentoProduct) {
                $magentoProduct->price = $item['price'];
            });

        $magentoProduct->save();
    }

    /**
     * @throws Exception
     */
    public static function fetchInventory(MagentoProduct $record)
    {
        $response = self::api($record->magentoConnection)->getStockItems($record->product->sku);

        if ($response === null) {
            throw new Exception('MAGENTO2API call returned null');
        }

        if ($response->notFound()) {
            $record->update(['exists_in_magento' => false]);
            return;
        }

        if ($response->failed()) {
            throw new Exception('Failed to fetch stock items for product '.$record->product->sku);
        }

        $record->stock_items_raw_import    = $response->json();
        $record->stock_items_fetched_at    = now();
        $record->remote_id                 = data_get($response->json(), 'item_id');
        $record->quantity                  = null;

        if (Arr::has($response->json(), 'qty')) {
            $record->quantity = data_get($response->json(), 'qty') ?: 0;
        }

        $record->save();
    }

    public static function updateInventory(MagentoConnection $magentoConnection, string $sku, float $quantity): ?Response
    {
        return self::api($magentoConnection)
            ->putStockItems($sku, [
                'is_in_stock' => $quantity > 0,
                'qty' => $quantity,
            ]);
    }

    /**
     * @throws Exception
     */
    public static function fetchFromInventorySourceItems(MagentoProduct $magentoProduct)
    {
        $response = self::api($magentoProduct->magentoConnection)
            ->getInventorySourceItems($magentoProduct->product->sku, $magentoProduct->magentoConnection->magento_inventory_source_code ?? 'all');

        if ($response === null) {
            throw new Exception('MAGENTO2API call returned null');
        }

        if ($response->failed()) {
            throw new Exception('Failed to fetch stock items for product '.$magentoProduct->product->sku);
        }

        $magentoProduct->stock_items_fetched_at = now();
        $magentoProduct->stock_items_raw_import = data_get($response->json(), 'items.0');
        $magentoProduct->remote_id = data_get($response->json(), 'items.0.item_id');
        $magentoProduct->quantity = data_get($response->json(), 'items.0.quantity');

        $magentoProduct->save();
    }
}
