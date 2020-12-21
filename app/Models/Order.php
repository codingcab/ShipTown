<?php

namespace App\Models;

use App\Traits\LogsActivityTrait;
use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int|null $shipping_address_id
 * @property string $order_number
 * @property string $status_code
 * @property string $total
 * @property string $total_paid
 * @property string|null $order_placed_at
 * @property string|null $order_closed_at
 * @property int $product_line_count
 * @property string|null $picked_at
 * @property string|null $packed_at
 * @property int|null $packer_user_id
 * @property string $total_quantity_ordered
 * @property array $raw_import
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property mixed $is_packed
 * @property-read mixed $is_paid
 * @property mixed $is_picked
 * @property-read Collection|OrderComment[] $orderComments
 * @property-read int|null $order_comments_count
 * @property-read Collection|OrderProduct[] $orderProducts
 * @property-read int|null $order_products_count
 * @property-read Collection|OrderShipment[] $orderShipments
 * @property-read int|null $order_shipments_count
 * @property-read User|null $packer
 * @property-read Collection|Packlist[] $packlist
 * @property-read int|null $packlist_count
 * @property-read OrderAddress|null $shippingAddress
 * @property-read OrderStats|null $stats
 * @method static \Illuminate\Database\Eloquent\Builder|Order active()
 * @method static \Illuminate\Database\Eloquent\Builder|Order addInventorySource($inventory_location_id)
 * @method static \Illuminate\Database\Eloquent\Builder|Order hasPacker($expected)
 * @method static \Illuminate\Database\Eloquent\Builder|Order isPacked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order isPacking($is_packing)
 * @method static \Illuminate\Database\Eloquent\Builder|Order isPicked($expected)
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereActive()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereHasText($text)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereIsNotPicked()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereIsPicked()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderPlacedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePackedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePackerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePickedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereProductLineCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereRawImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingAddressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatusCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalQuantityOrdered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Order extends Model
{
    use LogsActivityTrait;

    protected $fillable = [
        'order_number',
        'picked_at',
        'shipping_number',
        'shipping_address_id',
        'is_packed',
        'order_placed_at',
        'order_closed_at',
        'status_code',
        'packer_user_id',
        'raw_import',
        'total',
        'total_paid',
    ];

    protected static $logAttributes = [
        'status_code',
        'packer_user_id'
    ];

    protected $casts = [
        'raw_import' => 'array',
    ];

    // we use attributes to set default values
    // we wont use database default values
    // as this is then not populated
    // correctly to events
    protected $attributes = [
        'status_code' => 'processing',
        'raw_import' => '{}',
    ];

    protected $appends = [
        'is_picked',
        'is_packed',
        'age_in_days',
    ];

    /**
     * @param $query
     * @param $text
     * @return Builder
     */
    public function scopeWhereHasText($query, $text)
    {
        return $query->where('order_number', 'like', '%' . $text . '%')
            ->orWhere('status_code', '=', $text)
            ->orWhereHas('orderShipments', function ($query) use ($text) {
                return $query->where('shipping_number', 'like', '%'. $text . '%');
            });
    }

    /**
     * @return int
     */
    public function getAgeInDaysAttribute()
    {
        return Carbon::now()->ceilDay()->diffInDays($this->order_placed_at);
    }

    public function scopeWhereActive($query)
    {
        return $query->whereIn('status_code', OrderStatus::getActiveStatusCodesList());
    }

    /**
     * @param Builder $query
     * @param int $inventory_location_id
     * @return Builder
     */
    public function scopeAddInventorySource($query, $inventory_location_id)
    {
        $source_inventory = OrderProduct::query()
            ->select([
                'order_id as order_id',
                DB::raw('min(shelve_location) as min_shelf_location'),
                DB::raw('max(shelve_location) as max_shelf_location'),
            ])
            ->leftJoin('inventory', function ($join) use ($inventory_location_id) {
                $join->on('order_products.product_id', '=', 'inventory.product_id');
                $join->on('inventory.location_id', '=', DB::raw($inventory_location_id));
            })
            ->groupBy('order_products.order_id')
            ->toBase();

        return $query->leftJoinSub($source_inventory, 'inventory_source', function ($join) {
            $join->on('orders.id', '=', 'inventory_source.order_id');
        });
    }

    public function getIsPaidAttribute()
    {
        return ($this->total > 0) && ($this->total === $this->total_paid);
    }

    /**
     * @param $expected
     * @return bool
     */
    public function isNotStatusCode($expected)
    {
        return !$this->isStatusCode($expected);
    }

    /**
     * @param $expected
     * @return bool
     */
    public function isStatusCode($expected)
    {
        return $this->getAttribute('status_code') === $expected;
    }


    /**
     * @param array $statusCodes
     * @return bool
     */
    public function isStatusCodeNotIn(array $statusCodes)
    {
        return !$this->isStatusCodeIn($statusCodes);
    }

    /**
     * @param array $statusCodes
     * @return bool
     */
    public function isStatusCodeIn(array $statusCodes)
    {
        $statusCode = $this->getAttribute('status_code');

        return array_search($statusCode, $statusCodes) > -1;
    }

    /**
     * @param $query
     * @param bool $expected
     * @return self
     */
    public function scopeHasPacker($query, bool $expected)
    {
        if ($expected === false) {
            return $query->whereNull('packer_user_id');
        }

        return $query->whereNotNull('packer_user_id');
    }

    /**
     * @param $query
     * @param bool $expected
     * @return self
     */
    public function scopeIsPicked($query, bool $expected)
    {
        if ($expected === true) {
            return $query->whereIsPicked();
        }

        return $query->whereIsNotPicked();
    }

    /**
     * @param $query
     * @return self
     */
    public function scopeIsPacking($query, $is_packing)
    {
        if ($is_packing) {
            return $query->whereNotNull('packer_user_id');
        }

        return $query->whereNull('packer_user_id');
    }

    /**
     * @param $query
     * @return self
     */
    public function scopeWhereIsPicked($query)
    {
        return $query->whereNotNull('picked_at');
    }

    /**
     * @param $query
     * @return self
     */
    public function scopeWhereIsNotPicked($query)
    {
        return $query->whereNull('picked_at');
    }

    public function scopeIsPacked($query, $value)
    {
        return $query->whereNull('packed_at', 'and', $value);
    }

    public function getIsPackedAttribute()
    {
        return $this->packed_at !== null;
    }

    public function setIsPackedAttribute($value)
    {
        $this->packed_at = $value ? now() : null;
    }

    public function getIsPickedAttribute()
    {
        return $this->picked_at !== null;
    }

    public function setIsPickedAttribute($value)
    {
        $this->picked_at = $value ? now() : null;
    }

    public function scopeActive($query)
    {
        return $query->where('status_code', '=', 'processing');
    }

    /**
     * @return HasMany | OrderProduct
     */
    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    /**
     * @return HasMany | Packlist
     */
    public function packlist()
    {
        return $this->hasMany(Packlist::class);
    }

    /**
     * @return BelongsTo | OrderAddress
     */
    public function shippingAddress()
    {
        return $this->belongsTo(OrderAddress::class);
    }

    /**
     * @return BelongsTo | User
     */
    public function packer()
    {
        return $this->belongsTo(User::class, 'packer_user_id');
    }

    /**
     * @return HasMany | OrderShipment
     */
    public function orderShipments()
    {
        return $this->hasMany(OrderShipment::class)->latest();
    }
    /**
     * @return HasOne
     */
    public function stats()
    {
        return $this->hasOne(OrderStats::class);
    }

    /**
     * @return HasMany | OrderComment
     */
    public function orderComments()
    {
        return $this->hasMany(OrderComment::class)->latest();
    }

    /**
     * @return QueryBuilder
     */
    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(Order::class)
            ->allowedFilters([
                AllowedFilter::scope('search', 'whereHasText')->ignore([null, '']),

                AllowedFilter::exact('status', 'status_code'),
                AllowedFilter::exact('order_number')->ignore([null, '']),
                AllowedFilter::exact('packer_user_id'),

                AllowedFilter::scope('is_picked'),
                AllowedFilter::scope('is_packed'),
                AllowedFilter::scope('is_packing'),

                AllowedFilter::scope('has_packer'),

                AllowedFilter::scope('inventory_source_location_id', 'addInventorySource')
                    ->ignore([null, '']),
//                    ->default(100),
            ])
            ->allowedIncludes([
                'activities',
                'activities.causer',
                'stats',
                'shipping_address',
                'order_shipments',
                'order_products',
                'order_products.product',
                'order_products.product.aliases',
                'packer',
                'order_comments',
                'order_comments.user',
            ])
            ->allowedSorts([
                'updated_at',
                'product_line_count',
                'total_quantity_ordered',
                'order_placed_at',
                'order_closed_at',
                'min_shelf_location',
            ]);
    }
}
