<?php

namespace App\Modules\MagentoApi\src\Services;

use App\Modules\MagentoApi\src\Api\MagentoApi;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class MagentoService
{
    public static function api(MagentoConnection $magentoConnection = null): MagentoApi
    {
        /** @var MagentoConnection $magentoConnection */
        $magentoConnection = $magentoConnection ?? MagentoConnection::first();
        return new MagentoApi($magentoConnection);
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
    public static function fetchSpecialPrices(MagentoProduct $magentoProduct)
    {
        $response = self::api($magentoProduct->magentoConnection)
            ->postProductsSpecialPriceInformation($magentoProduct->product->sku);

        if ($response === null) {
            throw new Exception('MAGENTO2API fetchSpecialPrices returned null '.$magentoProduct->product->sku);
        }

        if ($response->notFound()) {
            $magentoProduct->update(['exists_in_magento' => false]);
            return;
        }

        if ($response->failed()) {
            throw new Exception('Failed to fetch sale prices for product '.$magentoProduct->product->sku);
        }

        $collect = collect($response->json());

        $specialPrices = $collect
            ->filter(function ($apiSpecialPriceRecord) use ($magentoProduct) {
                return $apiSpecialPriceRecord['store_id'] == $magentoProduct->magentoConnection->magento_store_id;
            });


        // magento sometimes randomly returns multiple special prices for the same store,
        // so we need to filter them out but only one is valid
        // randomizing result will match it sometimes, normally 3 special prices are returned,
        // so we will have statistically 1/3 chance to get the correct one,
        // it's a quick hack, but it works
        $specialPrices = $specialPrices->shuffle();

        $specialPrice = $specialPrices->first();

        Log::debug('Fetched special prices for product '.$magentoProduct->product->sku, [
            'special_prices' => $specialPrices->toArray(),
            'special_price' => $specialPrice,
        ]);

        if ($specialPrice) {
            $magentoProduct->magento_sale_price = $specialPrice['price'];
            $magentoProduct->magento_sale_price_start_date = $specialPrice['price_from'];
            $magentoProduct->magento_sale_price_end_date = $specialPrice['price_to'];
        }

        $magentoProduct->special_prices_fetched_at = now();
        $magentoProduct->special_prices_raw_import = $response->json();
        $magentoProduct->save();
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
                $magentoProduct->magento_price = $item['price'];
            });

        $magentoProduct->save();
    }

    /**
     * @throws Exception
     */
    public static function fetchInventory(MagentoProduct $magentoProduct)
    {
        if ($magentoProduct->magentoConnection->magento_inventory_source_code) {
            self::fetchFromInventorySourceItems($magentoProduct);
            return;
        }

        self::fetchStockItem($magentoProduct);
    }

    public static function updateInventory(MagentoConnection $magentoConnection, string $sku, float $quantity)
    {
        if ($magentoConnection->magento_inventory_source_code === null) {
            self::api($magentoConnection)
                ->putStockItems($sku, [
                    'is_in_stock' => $quantity > 0,
                    'qty' => $quantity,
                ]);
            return;
        }

        self::api($magentoConnection)->postInventorySourceItems($sku, $magentoConnection->magento_inventory_source_code, $quantity);
    }

    /**
     * @throws Exception
     */
    private static function fetchStockItem(MagentoProduct $magentoProduct)
    {
        $response = self::api($magentoProduct->magentoConnection)
            ->getStockItems($magentoProduct->product->sku);

        if ($response === null) {
            throw new Exception('MAGENTO2API call returned null');
        }

        if ($response->notFound()) {
            $magentoProduct->update(['exists_in_magento' => false]);
            return;
        }

        if ($response->failed()) {
            throw new Exception('Failed to fetch stock items for product '.$magentoProduct->product->sku);
        }

        $magentoProduct->stock_items_raw_import    = $response->json();
        $magentoProduct->stock_items_fetched_at    = now();
        $magentoProduct->quantity                  = null;

        if (Arr::has($response->json(), 'qty')) {
            $magentoProduct->quantity = data_get($response->json(), 'qty') ?: 0;
        }

        $magentoProduct->save();
    }

    /**
     * @throws Exception
     */
    private static function fetchFromInventorySourceItems(MagentoProduct $magentoProduct)
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

        if (data_get($response->json(), 'items.0')) {
            $magentoProduct->quantity = data_get($response->json(), 'items.0.quantity') ?: 0;
        } else {
            $magentoProduct->quantity = null;
        }

        $magentoProduct->save();
    }
}
