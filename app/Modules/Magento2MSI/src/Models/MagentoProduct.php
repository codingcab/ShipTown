<?php

namespace App\Modules\Magento2MSI\src\Models;

use App\BaseModel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property boolean $is_in_stock
 * @property double $quantity
 * @property Carbon $stock_items_fetched_at
 * @property array $stock_items_raw_import
 * @property Carbon $base_prices_fetched_at
 * @property array $base_prices_raw_import
 * @property MagentoConnection $magentoConnection
 * @property double $magento_price
 * @property Carbon $special_prices_fetched_at
 * @property array $special_prices_raw_import
 * @property double $magento_sale_price
 * @property Carbon $magento_sale_price_start_date
 * @property Carbon $magento_sale_price_end_date
 *
 * @property Product $product
 */
class MagentoProduct extends BaseModel
{
    protected $table = 'modules_magento2api_products';

    protected $fillable = [
        'product_id',
        'exists_in_magento',
        'connection_id',
        'is_in_stock',
        'quantity',
        'magento_price',
        'magento_sale_price',
        'stock_items_fetched_at',
        'stock_items_raw_import',
        'base_prices_fetched_at',
        'base_prices_raw_import',
        'special_prices_fetched_at',
        'special_prices_raw_import',
    ];
    protected $casts = [
        'magento_sale_price_start_date' => 'datetime',
        'magento_sale_price_end_date' => 'datetime',
        'stock_items_fetched_at' => 'datetime',
        'base_prices_fetched_at' => 'datetime',
        'special_prices_fetched_at' => 'datetime',
        'stock_items_raw_import'    => 'array',
        'base_prices_raw_import'    => 'array',
        'special_prices_raw_import'    => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function magentoConnection(): BelongsTo
    {
        return $this->belongsTo(MagentoConnection::class, 'connection_id');
    }
}
