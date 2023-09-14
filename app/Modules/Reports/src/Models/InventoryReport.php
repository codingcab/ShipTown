<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class InventoryReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Inventory Report';

        $this->baseQuery = Inventory::query()
            ->leftJoin('products as product', 'inventory.product_id', '=', 'product.id');

        $this->defaultSelect='warehouse_code,product_sku,product_name,quantity';

        $this->fields = [
            'warehouse_code'        => 'inventory.warehouse_code',
            'product_sku'           => 'product.sku',
            'product_name'          => 'product.name',
            'shelf_location'        => 'inventory.shelve_location',
            'quantity'              => 'inventory.quantity',
            'quantity_reserved'     => 'inventory.quantity_reserved',
            'quantity_available'    => 'inventory.quantity_available',
            'quantity_incoming'     => 'inventory.quantity_incoming',
            'restock_level'         => 'inventory.restock_level',
            'reorder_point'         => 'inventory.reorder_point',
            'quantity_required'     => 'inventory.quantity_required',
            'last_movement_at'      => 'inventory.last_movement_at',
            'first_received_at'     => 'inventory.first_received_at',
            'last_received_at'      => DB::raw('IFNULL(inventory.last_received_at,"2000-01-01 00:00:00")'),
            'first_sold_at'         => 'inventory.first_sold_at',
            'last_sold_at'          => 'inventory.last_sold_at',
            'last_counted_at'       => 'inventory.last_counted_at',
        ];

        $this->casts = [
            'quantity'              => 'float',
            'quantity_reserved'     => 'float',
            'quantity_available'    => 'float',
            'quantity_incoming'     => 'float',
            'restock_level'         => 'float',
            'reorder_point'         => 'float',
            'quantity_required'     => 'float',
            'last_movement_at'      => 'datetime',
            'first_received_at'     => 'datetime',
            'last_received_at'      => 'datetime',
            'first_sold_at'         => 'datetime',
            'last_sold_at'          => 'datetime',
            'last_counted_at'       => 'datetime',
        ];

        $this->addFilter(
            AllowedFilter::callback('has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->withAllTags($value);
                });
            })
        );
    }
}
