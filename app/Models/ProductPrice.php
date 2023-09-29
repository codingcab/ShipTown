<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\ProductPrice.
 *
 * @property int         $id
 * @property int         $product_id
 * @property int         $warehouse_id
 * @property string      $warehouse_code
 * @property int         $location_id
 * @property string      $price
 * @property string      $cost
 * @property string      $sale_price
 * @property Carbon      $sale_price_start_date
 * @property Carbon      $sale_price_end_date
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Product     $product
 * @property Warehouse   $warehouse
 *
 * @method static Builder|ProductPrice newModelQuery()
 * @method static Builder|ProductPrice newQuery()
 * @method static Builder|ProductPrice query()
 * @method static Builder|ProductPrice whereCreatedAt($value)
 * @method static Builder|ProductPrice whereDeletedAt($value)
 * @method static Builder|ProductPrice whereId($value)
 * @method static Builder|ProductPrice whereLocationId($value)
 * @method static Builder|ProductPrice whereWarehouseCode($value)
 * @method static Builder|ProductPrice wherePrice($value)
 * @method static Builder|ProductPrice whereProductId($value)
 * @method static Builder|ProductPrice whereSalePrice($value)
 * @method static Builder|ProductPrice whereSalePriceEndDate($value)
 * @method static Builder|ProductPrice whereSalePriceStartDate($value)
 * @method static Builder|ProductPrice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductPrice extends BaseModel
{
    protected $table = 'products_prices';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'location_id',
        'warehouse_code',
        'cost',
        'price',
        'sale_price',
        'sale_price_start_date',
        'sale_price_end_date',
    ];

    // we use attributes to set default values
    // we wont use database default values
    // as this is then not populated
    // correctly to events
    protected $attributes = [
        'price'                 => 99999,
        'sale_price'            => 99999,
        'sale_price_start_date' => '2001-01-01 00:00:00',
        'sale_price_end_date'   => '2001-01-01 00:00:00',
    ];

    protected $casts = [
        'sale_price_start_date' => 'datetime',
        'sale_price_end_date'   => 'datetime',
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
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
