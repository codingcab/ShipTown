<?php

namespace App\Modules\MagentoApi\src\Models;

use App\BaseModel;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Modules\InventoryTotals\src\Models\InventoryTotalByWarehouseTag;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Modules\MagentoApi\src\Models\MagentoProduct
 * @property int $id
 * @property int $connection_id
 * @property int $product_id
 * @property int $inventory_totals_by_warehouse_tag_id
 * @property int $product_price_id
 * @property-read MagentoConnection $magentoConnection
 * @property-read InventoryTotalByWarehouseTag $inventoryTotalsByWarehouseTag
 * @property bool $exists_in_magento
 * @property int|null $remote_id
 * @property bool $is_in_stock
 * @property float $quantity
 * @property float $price
 *  @property float $sale_price
 * @property Carbon|null $stock_items_fetched_at
 * @property Carbon|null $base_prices_fetched_at
 * @property Carbon|null $special_prices_fetched_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $product
 * @property-read ProductPrice $prices
 * @property array|mixed $stock_items_raw_import
 * @property array|mixed $base_prices_raw_import
 * @property Carbon $sale_price_start_date
 * @property Carbon $sale_price_end_date
 * @property array|mixed $special_prices_raw_import
 *
 * @property Carbon|null $inventory_synced_at
 * @property Carbon|null $pricing_synced_at
 * @property Carbon|null $special_prices_synced_at
 *
 */
class MagentoProduct extends BaseModel
{
    protected $table = 'modules_magento2api_products';

    protected $fillable = [
        'connection_id',
        'product_id',
        'inventory_totals_by_warehouse_tag_id',
        'product_price_id',
        'exists_in_magento',
        'remote_id',
        'quantity',
        'price',
        'sale_price',
        'sale_price_start_date',
        'sale_price_end_date',
        'inventory_synced_at',
        'pricing_synced_at',
        'sale_prices_synced_at',
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

    public function inventoryTotalsByWarehouseTag(): HasOne
    {
        return $this->hasOne(InventoryTotalByWarehouseTag::class, 'id', 'inventory_totals_by_warehouse_tag_id');
    }

    public function prices(): HasOne
    {
        return $this->hasOne(ProductPrice::class, 'id', 'product_price_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function magentoConnection(): BelongsTo
    {
        return $this->belongsTo(MagentoConnection::class, 'connection_id');
    }
}
