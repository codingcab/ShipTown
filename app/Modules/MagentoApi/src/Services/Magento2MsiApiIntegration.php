<?php

namespace App\Modules\MagentoApi\src\Services;

use App\Modules\MagentoApi\src\Abstracts\EcommerceIntegration;
use App\Modules\MagentoApi\src\Api\Magento2Api;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use Exception;
use Illuminate\Support\Collection;

class Magento2MsiApiIntegration extends EcommerceIntegration
{
    public static function fetchInventory(MagentoConnection $apiConnection, Collection $recordCollection): bool
    {
        $skus = [
            'field' => 'sku',
            'value' => $recordCollection->implode('product.sku', ','),
            'condition_type' => 'in'
        ];

        $filterGroups = [
            [
                'filters' => [
                    $skus
                ]
            ],
            [
                'filters' => [
                    [
                        'field' => 'source_code',
                        'value' => 'source_limerick',
                        'condition_type' => 'in'
                    ]
                ]
            ]
        ];
        $response = Magento2Api::api($apiConnection)->get('inventory/source-items', [
                'searchCriteria' => [
                    'filterGroups' => $filterGroups
                ],
            ]);

        if ($response === null) {
            return false;
        }

        if ($response->notFound()) {
            $recordCollection->map(function (MagentoProduct $record) {
                $record->update(['exists_in_magento' => false]);
            });
            return true;
        }

        if ($response->failed()) {
            throw new Exception('Failed to fetch stock items for product');
        }

        return true;
    }

    public static function fetchBasePrices(Collection $recordCollection): bool
    {
        return true;
    }

    public static function fetchSpecialPrices(Collection $recordCollection): bool
    {
        return true;
    }

    public static function updateInventory(MagentoConnection $apiConnection, Collection $recordCollection): bool
    {
        $sourceItems = $recordCollection
            ->map(function (MagentoProduct $record) use ($apiConnection) {
                return [
                    'source_code' => $apiConnection->magento_inventory_source_code,
                    'sku' => $record->product->sku,
                    'quantity' => $record->inventoryTotalsByWarehouseTag->quantity_available,
                    'status' => 1,
                ];
            })->toArray();

        Magento2Api::api($apiConnection)->post('inventory/source-items', [
            'sourceItems' => $sourceItems
        ]);
        return true;
    }

    public static function updateBasePrices(Collection $recordCollection): bool
    {
        return true;
    }

    public static function updateSalePrices(Collection $recordCollection): bool
    {
        return true;
    }
}
