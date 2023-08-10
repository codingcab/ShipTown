<?php

namespace App\Modules\MagentoApi\src\Models;

use App\BaseModel;
use App\Models\Warehouse;
use App\Traits\HasTagsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property string $base_url
 * @property string $api_access_token
 * @property integer $magento_store_id
 * @property integer inventory_source_warehouse_tag_id
 * @property Carbon $deleted_at
 * @property Carbon $updated_at
 * @property Carbon $created_at
 */
class MagentoConnection extends BaseModel
{
    use HasTagsTrait;

    protected $table = 'modules_magento2api_connections';

    protected $fillable = [
        'base_url',
        'api_access_token',
        'magento_store_id',
        'inventory_source_warehouse_tag_id',
        'pricing_source_warehouse_id',
    ];

    public function getApiAccessTokenAttribute(): string
    {
        return Crypt::decryptString($this->attributes['access_token_encrypted']);
    }

    public function setApiAccessTokenAttribute($value)
    {
        $this->attributes['access_token_encrypted'] = Crypt::encryptString($value);
    }

    public function getAccessTokenAttribute(): string
    {
        return Crypt::decryptString($this->attributes['access_token_encrypted']);
    }

    public function setAccessTokenAttribute($value)
    {
        $this->attributes['access_token_encrypted'] = Crypt::encryptString($value);
    }

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
}
