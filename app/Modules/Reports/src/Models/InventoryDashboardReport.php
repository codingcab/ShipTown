<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Inventory;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class InventoryDashboardReport extends Report
{
    use LogsActivityTrait;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->view = 'reports.inventory-dashboard';

        $this->report_name = 'Inventory Dashboard';

        if (request('title')) {
            $this->report_name = request('title').' ('.$this->report_name.')';
        }

        $this->fields = [
            'warehouse_id'               => 'inventory.warehouse_id',
            'warehouse_code'             => 'inventory.warehouse_code',
            'missing_restock_levels'     => DB::raw('count(CASE WHEN inventory.restock_level <= 0 THEN 1 END)'),
            'wh_products_available'      => DB::raw('count(*)'),
            'wh_products_out_of_stock'   => DB::raw('count(CASE WHEN inventory.quantity_available = 0 AND inventory.restock_level > 0 THEN 1 END)'),
            'wh_products_required'       => DB::raw('count(CASE WHEN inventory.quantity_required > 0 THEN 1 END)'),
            'wh_products_incoming'       => DB::raw('count(CASE WHEN inventory.quantity_incoming > 0 THEN 1 END)'),
            'wh_products_stock_level_ok' => DB::raw('count(CASE ' .
                'WHEN (inventory.quantity_required = 0 AND inventory.restock_level > 0) ' .
                'THEN 1 ' .
                'END)'),
        ];

        $this->baseQuery = Inventory::query()
            ->rightJoin('inventory as inventory_source', function (JoinClause $join) {
                $join->on('inventory_source.product_id', '=', 'inventory.product_id');
                $join->on('inventory_source.warehouse_id', '=', DB::raw(2));
                $join->where('inventory_source.quantity_available', '>', 0);
            })
            ->leftJoin('products as product', 'inventory.product_id', '=', 'product.id')
            ->where('inventory_source.warehouse_code', '=', '99')
            ->where('inventory_source.quantity_available', '>', 0)
            ->whereNotIn('inventory.warehouse_code', ['99', '100'])
            ->groupBy('inventory.warehouse_code', 'inventory.warehouse_id');

        $this->setPerPage(100);

        $this->casts = [
            'warehouse_id'                => 'integer',
            'warehouse_code'              => 'string',
            'wh_products_available'       => 'float',
            'wh_products_out_of_stock'    => 'float',
            'wh_products_required'        => 'float',
            'wh_products_incoming'        => 'float',
            'wh_products_stock_level_ok'  => 'float',
        ];

        $this->addFilter(
            AllowedFilter::callback('has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->withAllTags($value);
                });
            })
        );
    }

    public function saveSnapshotToActivity()
    {
        $this->log('snapshot', $this->queryBuilder()->get()->toArray());

        return $this;
    }
}
