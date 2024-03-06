<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderPicklistResource;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class PicklistController.
 */
class PicklistController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderProduct::getSpatieQueryBuilder()
            ->where('quantity_to_pick', '>', 0)
            ->select([
                'product_id',
                'name_ordered',
                'sku_ordered',
                'inventory_source_shelf_location',
                DB::raw('sum(`quantity_to_pick`) as total_quantity_to_pick'),
                DB::raw('max(`inventory_source_quantity`) as inventory_source_quantity'),
                DB::raw("GROUP_CONCAT(id ORDER BY id SEPARATOR ',' ) AS order_product_ids"),
                DB::raw("GROUP_CONCAT(order_id ORDER BY order_id SEPARATOR ',' ) AS order_ids"),
            ])
            ->groupBy([
                'orders_products.name_ordered',
                'orders_products.sku_ordered',
                'orders_products.product_id',
                'inventory_source_shelf_location',
            ]);

        return OrderPicklistResource::collection($this->getPaginatedResult($query));
    }
}
