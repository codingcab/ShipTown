<?php

namespace App\Modules\InventoryReservations\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryReservation extends Model
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
}
