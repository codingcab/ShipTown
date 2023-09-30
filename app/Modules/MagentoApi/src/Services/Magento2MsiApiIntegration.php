<?php

namespace App\Modules\MagentoApi\src\Services;

use App\Modules\MagentoApi\src\Abstracts\EcommerceIntegration;
use Illuminate\Support\Collection;

class Magento2MsiApiIntegration extends EcommerceIntegration
{
    public static function fetchInventory(Collection $recordCollection): bool
    {
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

    public static function updateInventory(Collection $recordCollection): bool
    {
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
