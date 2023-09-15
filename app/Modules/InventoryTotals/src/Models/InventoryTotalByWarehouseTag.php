<?php

namespace App\Modules\InventoryTotals\src\Models;

use App\BaseModel;

class InventoryTotalByWarehouseTag extends BaseModel
{
    protected $table = 'inventory_totals_by_warehouse_tag';

    protected $fillable = [
        'tag_id',
        'product_id',
        'quantity',
        'quantity_reserved',
        'quantity_incoming',
    ];
}
