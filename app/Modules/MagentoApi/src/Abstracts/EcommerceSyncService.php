<?php

namespace App\Modules\MagentoApi\src\Abstracts;

use App\Modules\MagentoApi\src\Models\MagentoProduct;

abstract class EcommerceSyncService
{
    abstract public static function fetchFromInventorySourceItems(MagentoProduct $magentoProduct);
}
