<?php

namespace App\Modules\InventoryTotals\src\Models;

use App\BaseModel;

/**
 * Configuration
 * @property int $totals_max_product_id_checked
 * @property int totals_by_warehouse_tag_max_inventory_id_checked
 *
 */
class Configuration extends BaseModel
{
    protected $table = 'modules_inventory_totals_configurations';

    protected $fillable = [
        'totals_max_product_id_checked',
        'totals_by_warehouse_tag_max_inventory_id_checked',
    ];

    protected $attributes = [
        'totals_max_product_id_checked' => 0,
        'totals_by_warehouse_tag_max_inventory_id_checked' => 0,
    ];
}
