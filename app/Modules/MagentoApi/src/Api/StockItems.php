<?php

namespace App\Modules\MagentoApi\src\Api;

use Grayloon\Magento\Api\AbstractApi;
use Illuminate\Http\Client\Response;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Utils;

class StockItems extends AbstractApi
{
    public function postProductsSpecialPriceInformation($sku): Response
    {
        return $this->post('/products/special-price-information', [
            'skus' => [$sku]
        ]);
    }

    public function postProductsBasePricesInformation($sku): Response
    {
        return $this->post('/products/base-prices-information', [
            'skus' => [$sku]
        ]);
    }

    public function putStockItems($sku, $params)
    {
        return $this->put('/products/'.$sku.'/stockItems/0', [
            'stockItem' => $params,
        ]);
    }

    public function getStockItems($sku): Response
    {
        return $this->get('/stockItems/'.$sku);
    }

    public function getInventorySourceItems($sku, $storeCode): Response
    {
        return $this->get('/inventory/source-items', [
            'searchCriteria' => [
                'filterGroups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'sku',
                                'value' => $sku,
                                'condition_type' => 'in'
                            ]
                        ]
                    ],
                    [
                        'filters' => [
                            [
                                'field' => 'source_code',
                                'value' => $storeCode,
                                'condition_type' => 'in'
                            ]
                        ]
                    ]
                ]
            ],
        ]);
    }

    public function postInventorySourceItems($sku, $storeCode, $quantity): Response
    {
        return $this->post('/inventory/source-items', [
            'sourceItems' => [
                [
                    'source_code' => $storeCode,
                    'sku' => $sku,
                    'quantity' => $quantity,
                    'status' => 1,
                ]
            ],
        ]);
    }

    public function postProductsBasePrices(string $sku, float $price, int $store_id): Response
    {
        return $this->post('/products/base-prices', [
            'prices' => [
                [
                'sku' => $sku,
                'price' => $price,
                'store_id' => $store_id,
                ]
            ]
        ]);
    }
}
