<?php

namespace App\Modules\Integrations\Magento2MSI\src\Api\Products;

use Grayloon\Magento\Api\AbstractApi;
use Illuminate\Http\Client\Response;

/**
 * Class BasePrices
 * @package App\Modules\MagentoApi\src\Api\Products
 */
class BasePrices extends AbstractApi
{
    /**
     * @param $sku
     * @param $price
     * @param $store_id
     * @return Response
     */
    public function update($sku, $price, $store_id): Response
    {
        return $this->post('/products/base-prices', [
            'prices' => [
                [
                    'price' => $price,
                    'sku' => $sku,
                    'store_id' => $store_id
                ]
            ]
        ]);
    }
}
