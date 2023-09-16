<?php

namespace App\Modules\InventoryTotals\src\Models;

use App\BaseModel;

class InventoryTotal extends BaseModel
{
    protected $table = 'inventory_totals';

    protected $fillable = [
        'product_id',
        'quantity',
        'quantity_reserved',
        'quantity_incoming',
        'max_inventory_updated_at',
        'calculated_at',
    ];
}
