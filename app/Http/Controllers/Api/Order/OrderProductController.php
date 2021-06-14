<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderProducts\UpdateRequest;
use App\Http\Resources\OrderProductResource;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class OrderProductController
 * @package App\Http\Controllers\Api\Order
 *
 * @group Order
 */
class OrderProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = OrderProduct::getSpatieQueryBuilder();

        return OrderProductResource::collection($this->getPaginatedResult($query));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param $id
     * @return OrderProductResource
     */
    public function update(UpdateRequest $request, $id): OrderProductResource
    {
        $orderProduct = OrderProduct::findOrFail($id);

        $orderProduct->update($request->validated());

        return new OrderProductResource($orderProduct);
    }
}
