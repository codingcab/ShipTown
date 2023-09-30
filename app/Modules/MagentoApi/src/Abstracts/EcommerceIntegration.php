<?php

namespace App\Modules\MagentoApi\src\Abstracts;

use App\Modules\MagentoApi\src\Models\MagentoConnection;
use Exception;
use Illuminate\Support\Collection;

abstract class EcommerceIntegration
{
    /**
     * @throws Exception
     */
    abstract public static function fetchInventory(MagentoConnection $apiConnection, Collection $recordCollection): bool;

    /**
     * @throws Exception
     */
    abstract public static function fetchBasePrices(Collection $recordCollection): bool;

    /**
     * @throws Exception
     */
    abstract public static function fetchSpecialPrices(Collection $recordCollection): bool;

    /**
     * @throws Exception
     */
    abstract public static function updateInventory(MagentoConnection $apiConnection, Collection $recordCollection): bool;

    /**
     * @throws Exception
     */
    abstract public static function updateBasePrices(Collection $recordCollection): bool;

    /**
     * @throws Exception
     */
    abstract public static function updateSalePrices(Collection $recordCollection): bool;
}
