<?php

namespace App\Modules\MagentoApi\src\Models;

use App\BaseModel;
use App\Models\Warehouse;
use App\Traits\HasTagsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * App\Modules\MagentoApi\src\Models\MagentoConnection
 * @property int $id
 * @property bool $is_enabled
 * @property string $base_url
 * @property int $magento_store_id
 * @property string $magento_store_code
 * @property string $magento_inventory_source_code
 * @property int $inventory_totals_tag_id
 * @property int $pricing_source_warehouse_id
 * @property string $api_access_token
 * @property Carbon|null $deleted_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 *
 * @property Warehouse $warehouse
 * @property Collection $tags
 */
class MagentoConnection extends BaseModel
{
    use HasTagsTrait;
    use HasFactory;

    protected $table = 'modules_magento2api_connections';

    protected $fillable = [
        'is_enabled',
        'base_url',
        'magento_store_id',
        'magento_store_code',
        'magento_inventory_source_code',
        'inventory_totals_tag_id',
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
