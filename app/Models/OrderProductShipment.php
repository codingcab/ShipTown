<?php

namespace App\Models;

use App\BaseModel;
use App\Traits\LogsActivityTrait;
use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * App\Models\OrderProduct.
 *
 * @property int         $id
 * @property int|null    $product_id
 * @property int|null    $user_id
 * @property int|null    $warehouse_id
 * @property int|null    $order_id
 * @property int|null    $order_product_id
 * @property float       $quantity_shipped
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Product|null  $product
 * @property Warehouse|null  $warehouse
 * @property User|null   $user
 * @property Order|null  $order
 * @property OrderProduct|null  $orderProduct
 *
 * @mixin Eloquent
 */
class OrderProductShipment extends BaseModel
{
    protected $table = 'orders_products_shipments';

    protected $fillable = [
        'products_id',
        'user_id',
        'warehouse_id',
        'order_id',
        'order_product_id',
        'quantity_shipped',
        'order_shipment_id',
    ];

    protected $casts = [
        'quantity_shipped' => 'float',
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo
     */
    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
