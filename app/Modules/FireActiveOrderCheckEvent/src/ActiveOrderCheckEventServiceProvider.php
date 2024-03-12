<?php

namespace App\Modules\FireActiveOrderCheckEvent\src;

use App\Modules\BaseModuleServiceProvider;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class ActiveOrderCheckEventServiceProvider extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = '.CORE - Active Order Check';

    /**
     * @var string
     */
    public static string $module_description = 'Run automations 5 minutes after the order is created';

    /**
     * @var bool
     */
    public static bool $autoEnable = true;

    /**
     * @var array
     */
    protected $listen = [
    ];

    public static function disabling(): bool
    {
        return false;
    }
}
