<?php

namespace App\Modules\InventoryMovements\src\Models;

use App\BaseModel;

/**
 * @property integer $quantity_before_job_last_movement_id_checked
 */
class Configuration extends BaseModel
{
    protected $table = 'modules_inventory_movements_configurations';

    protected $fillable = [
        'quantity_before_job_last_movement_id_checked',
    ];

    protected $casts = [
        'quantity_before_job_last_movement_id_checked' => 'integer',
    ];
}
