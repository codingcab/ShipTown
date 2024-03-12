<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Listeners;

use App\Events\Order\OrderUpdatedEvent;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\OrderProduct;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;

class OrderUpdatedEventListener
{
    /**
     * Handle the event.
     *
     * @param OrderUpdatedEvent $event
     *
     * @return void
     */
    public function handle(OrderUpdatedEvent $event)
    {
        if ($event->order->isAttributeNotChanged('is_active')) {
            return;
        }

        $inventoryReservations = InventoryReservation::where('custom_uuid', 'like', 'order_id_'.$event->order->getKey().'%')->get();
        $inventoryIds = $inventoryReservations->pluck('inventory_id')->toArray();
        // delete all reservations for this order
        $inventoryReservations->each->delete();

        if ($event->order->is_active) {
            $config = Configuration::first();
            $orderProducts = $event->order->orderProducts->whereNotNull('product_id');

            $dataToAdd = $orderProducts->map(function (OrderProduct $orderProduct) use ($event, $config) {

                $uuid = ReservationsService::generateOrderProductUuid($event->order->getKey(), $orderProduct->getKey());

                $inventory = Inventory::where('product_id', $orderProduct->product_id)
                    ->where('warehouse_id', $config->warehouse_id)
                    ->first(['id', 'warehouse_code']);

                return [
                    'inventory_id' => $inventory->id,
                    'product_sku' => $orderProduct->sku_ordered,
                    'warehouse_code' => $inventory->warehouse_code,
                    'quantity_reserved' => $orderProduct->quantity_to_ship,
                    'comment' => 'Order #' . $event->order->order_number,
                    'custom_uuid' => $uuid,
                ];
            });

            $inventoryIds = $dataToAdd->pluck('inventory_id')->unique()->toArray();
            InventoryReservation::create($dataToAdd->toArray());
        }

        ReservationsService::recalculateTotalQuantityReserved($inventoryIds);
    }
}
