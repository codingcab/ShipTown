<?php

namespace App\Modules\Integrations\Magento2MSI\src\Models;

use App\BaseModel;
use App\Traits\HasTagsTrait;
use Illuminate\Support\Facades\Crypt;

/**
 * @property string $base_url
 * @property string $api_access_token
 * @property string $store_code
 * @property integer inventory_source_warehouse_tag_id
 */
class MagentoConnection extends BaseModel
{
    use HasTagsTrait;

    protected $table = 'modules_magento2api_connections';

    protected $fillable = [
        'base_url',
        'api_access_token',
        'store_code',
        'inventory_source_warehouse_tag_id',
    ];

    public function getApiAccessTokenAttribute(): string
    {
        return Crypt::decryptString($this->attributes['access_token_encrypted']);
    }

    public function setApiAccessTokenAttribute($value): void
    {
        $this->attributes['access_token_encrypted'] = Crypt::encryptString($value);
    }
}
