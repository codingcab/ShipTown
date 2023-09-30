<?php

namespace App\Modules\MagentoApi\src\Models;

use App\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property MagentoProduct $magentoProduct
 * @property MagentoConnection $magentoConnection
 * @property integer $magento_store_id
 * @property string $sku
 * @property double $expected_price
 * @property double $expected_sale_price
 * @property Carbon $expected_sale_price_start_date
 * @property Carbon $expected_sale_price_end_date
 */
class MagentoProductPricesComparisonView extends BaseModel
{
    protected $table = 'modules_magento2api_products_prices_comparison_view';

    protected $casts = [
        'expected_price' => 'double',
        'expected_sale_price' => 'double',
        'magento_sale_price_start_date' => 'datetime',
        'expected_sale_price_start_date' => 'datetime',
        'magento_sale_price_end_date' => 'datetime',
        'expected_sale_price_end_date' => 'datetime',
    ];

    public function magentoProduct(): BelongsTo
    {
        return $this->belongsTo(MagentoProduct::class, 'modules_magento2api_products_id');
    }

    public function magentoConnection(): BelongsTo
    {
        return $this->belongsTo(MagentoConnection::class, 'modules_magento2api_connection_id');
    }
}
