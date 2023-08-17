<?php

namespace App\Modules\MagentoApi\src\Models;

use App\BaseModel;
use App\Models\Warehouse;
use App\Traits\HasTagsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property integer $id
 * @property string $base_url
 * @property integer $magento_store_id
 * @property string  $magento_store_code
 * @property integer $magento_inventory_source_code
 * @property integer $inventory_source_warehouse_tag_id
 * @property integer $pricing_source_warehouse_id
 * @property string $api_access_token
 * @property Carbon $deleted_at
 * @property Carbon $updated_at
 * @property Carbon $created_at
 *
 * @property Warehouse $warehouse
 * @property Collection $tags
 */
class MagentoConnection extends BaseModel
{
    use HasTagsTrait;

    protected $table = 'modules_magento2api_connections';

    protected $fillable = [
        'base_url',
        'magento_store_id',
        'magento_store_code',
        'magento_inventory_source_code',
        'inventory_source_warehouse_tag_id',
        'pricing_source_warehouse_id',
        'api_access_token',
    ];

    public static function getSpatieQueryBuilder(): QueryBuilder
    {
        return QueryBuilder::for(MagentoConnection::class)
            ->allowedIncludes([
                'tags','warehouse'
            ]);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'pricing_source_warehouse_id', 'id');
    }

    public function getApiAccessTokenAttribute(): string
    {
        return Crypt::decryptString($this->attributes['access_token_encrypted']);
    }

    public function setApiAccessTokenAttribute($value)
    {
        $this->attributes['access_token_encrypted'] = Crypt::encryptString($value);
    }
}
