<?php

namespace App\Models;

use App\BaseModel;
use App\Events\Warehouse\WarehouseTagAttachedEvent;
use App\Traits\HasTagsTrait;
use App\Traits\LogsActivityTrait;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Models\Warehouse.
 *
 * @property int                $id
 * @property string             $code
 * @property string             $name
 * @property int|null           $address_id
 * @property string|null        $deleted_at
 * @property Carbon|null        $created_at
 * @property Carbon|null        $updated_at
 *
 * @property OrderAddress|null  $address
 *
 * @method static Builder|Warehouse newModelQuery()
 * @method static Builder|Warehouse newQuery()
 * @method static Builder|Warehouse query()
 * @method static Builder|Warehouse whereCode($value)
 * @method static Builder|Warehouse whereCreatedAt($value)
 * @method static Builder|Warehouse whereDeletedAt($value)
 * @method static Builder|Warehouse whereId($value)
 * @method static Builder|Warehouse whereName($value)
 * @method static Builder|Warehouse whereUpdatedAt($value)
 *
 * @mixin Eloquent
 * @mixin HasTagsTrait
 */
class Warehouse extends BaseModel
{
    use HasFactory;

    use LogsActivityTrait;
    use HasTagsTrait;

    protected $fillable = [
        'code',
        'name',
        'address_id',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * @return QueryBuilder
     */
    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(Warehouse::class)
            ->allowedFilters([
                AllowedFilter::scope('search', 'whereHasText'),
                AllowedFilter::exact('id'),
                AllowedFilter::exact('code'),
                AllowedFilter::exact('name'),

                AllowedFilter::scope('has_tags', 'hasTags'),
                AllowedFilter::scope('without_tags', 'withoutAllTags'),
            ])
            ->allowedSorts([
                'id',
                'code',
                'name',
            ])
            ->allowedIncludes([
                'address',
                'tags',
            ]);
    }

    /**
     * @return BelongsTo
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(OrderAddress::class);
    }

    protected function onTagAttached($tag)
    {
        WarehouseTagAttachedEvent::dispatch($this, $tag);
    }

    protected function onTagDetached($tag)
    {
        WarehouseTagAttachedEvent::dispatch($this, $tag);
    }
}
