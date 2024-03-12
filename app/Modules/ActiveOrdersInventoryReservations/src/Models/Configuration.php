<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Models;

use App\BaseModel;

/**
 * @property integer $warehouse_id
 */
class Configuration extends BaseModel
{
    protected $table = 'modules_inventory_reservations_configurations';

    protected $fillable = [
        'warehouse_id',
    ];
}
