<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 *  DataCollection
 * @property int $id
 * @property int $warehouse_id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DataCollection extends Model
{
    protected $fillable = [
        'warehouse_id',
        'name',
    ];
}
