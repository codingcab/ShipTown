<?php

namespace App\Modules\MagentoApi\src\Models;

use App\BaseModel;

/**
 * Class MagentoConfiguration
 * @property int $id
 * @property int $last_product_id_checked
 */
class MagentoConfiguration extends BaseModel
{
    protected $table = 'modules_magento2api_configurations';

    protected $fillable = [
        'last_product_id_checked',
    ];
}
