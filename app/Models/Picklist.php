<?php

namespace App\Models;

use DateTime;
use Hulkur\HasManyKeyBy\HasManyKeyByRelationship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property float quantity_picked
 * @property DateTime|null picked_at
 * @property Order|null order
 */
class Picklist extends Model
{
    use SoftDeletes;
    use HasManyKeyByRelationship;

    protected $fillable = [
        'order_id',
        'order_product_id',
        'product_id',
        'location_id',
        'sku_ordered',
        'name_ordered',
        'quantity_requested',
        'quantity_picked',
        'picker_user_id',
        'picked_at',
    ];

    public static function addPick($params) {
        return self::query()->create($params);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function inventory()
    {
        return $this->hasMany(
            Inventory::class,
            'product_id',
            'product_id'
        )->keyBy('location_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'picker_user_id');
    }

    public function wasPickSkipped()
    {
        return $this->quantity_picked == 0 && $this->picked_at != null;
    }
}
