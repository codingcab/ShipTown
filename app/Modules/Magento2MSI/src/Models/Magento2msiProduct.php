<?php

namespace App\Modules\Magento2MSI\src\Models;

use App\BaseModel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 * @property Product $product
 */
class Magento2msiProduct extends BaseModel
{
    protected $table = 'modules_magento2msi_inventory_source_items';

    protected $fillable = [
        'connection_id',
        'product_id',
        'inventory_totals_by_warehouse_tag_id',
        'sync_required',
        'custom_uuid',
        'sku',
        'source_code',
        'quantity',
        'status',
        'inventory_source_items_fetched_at',
        'inventory_source_items',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'inventory_source_items_fetched_at' => 'datetime',
        'inventory_source_items'            => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function magento2msiConnection(): BelongsTo
    {
        return $this->belongsTo(Magento2msiConnection::class, 'connection_id');
    }
}
