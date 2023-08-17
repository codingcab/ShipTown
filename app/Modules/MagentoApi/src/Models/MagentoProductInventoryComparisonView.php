<?php

namespace App\Modules\MagentoApi\src\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property MagentoProduct $magentoProduct
 * @property MagentoConnection $magentoConnection
 * @property double $expected_quantity
 */
class MagentoProductInventoryComparisonView extends BaseModel
{
    protected $table = 'modules_magento2api_products_inventory_comparison_view';

    public function magentoProduct(): BelongsTo
    {
        return $this->belongsTo(MagentoProduct::class, 'modules_magento2api_products_id');
    }

    public function magentoConnection(): BelongsTo
    {
        return $this->belongsTo(MagentoConnection::class, 'modules_magento2api_connection_id');
    }
}
