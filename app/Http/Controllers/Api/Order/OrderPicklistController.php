<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderPicklist\StoreRequest;
use App\Http\Resources\OrderPicklistResource;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class OrderPicklistController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderProduct::getSpatieQueryBuilder()
            ->select([
                'order_products.product_id',
                'order_products.name_ordered',
                'order_products.sku_ordered',
                DB::raw("sum(`". DB::getTablePrefix() ."order_products`.`quantity_to_pick`) as total_quantity_to_pick"),
                DB::raw("max(`inventory_source_quantity`) as inventory_source_quantity"),
                'inventory_source_shelf_location',
                DB::raw("GROUP_CONCAT(". DB::getTablePrefix() ."order_products.id ORDER BY ". DB::getTablePrefix() ."order_products.id SEPARATOR ',' ) AS order_product_ids"),
            ])
            ->groupBy([
                'order_products.name_ordered',
                'order_products.sku_ordered',
                'order_products.product_id',
                'inventory_source_shelf_location',
            ]);

        return OrderPicklistResource::collection($this->getPerPageAndPaginate($request, $query, 10));
    }

    public function store(StoreRequest $request)
    {
        $orders = OrderProduct::where($request->only(['name_ordered','sku_ordered']))
            ->get();

        return JsonResource::collection($orders);
    }
}
