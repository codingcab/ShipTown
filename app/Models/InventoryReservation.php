<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property integer $inventory_id
 */

class InventoryReservation extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'product_sku',
        'warehouse_code',
        'quantity_reserved',
        'comment',
        'custom_uuid'
    ];

    protected $casts = [
        'quantity_reserved' => 'float'
    ];
}
