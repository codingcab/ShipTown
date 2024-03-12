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
    public function handle(OrderUpdatedEvent $event): void
    {
        if ($event->order->isAttributeNotChanged('is_active')) {
            return;
        }

        if ($event->order->is_active) {
            $this->reserveInventoryForAllOrderProducts($event);
        } else {
            $uuidPrefix = 'order_id_' . $event->order->getKey();

            InventoryReservation::query()
                ->where('custom_uuid', 'like', $uuidPrefix .'%')
                ->get()
                ->each
                ->delete();
        }
    }

    public function reserveInventoryForAllOrderProducts(OrderUpdatedEvent $event): void
    {
        /** @var Configuration $config */
        $config = Configuration::first();
        $orderProducts = $event->order->orderProducts->whereNotNull('product_id');

        $dataToAdd = $orderProducts->map(function (OrderProduct $orderProduct) use ($event, $config) {
            $uuid = ReservationsService::generateOrderProductUuid($orderProduct);

            $inventory = Inventory::query()
                ->where('product_id', $orderProduct->product_id)
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

        InventoryReservation::query()->create($dataToAdd->toArray());
    }
}
