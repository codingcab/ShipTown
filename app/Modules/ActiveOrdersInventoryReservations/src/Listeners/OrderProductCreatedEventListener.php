<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Listeners;

use App\Events\OrderProduct\OrderProductCreatedEvent;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;

class OrderProductCreatedEventListener
{
    public function handle(OrderProductCreatedEvent $event): void
    {
        if ($event->orderProduct->product_id === null) {
            return;
        }

        /** @var Configuration $config */
        $config = Configuration::first();

        $inventory = Inventory::query()
            ->where('product_id', $event->orderProduct->product_id)
            ->where('warehouse_id', $config->warehouse_id)
            ->first(['id', 'warehouse_code']);

        $uuid = ReservationsService::generateOrderProductUuid($event->orderProduct->order->getKey(), $event->orderProduct->getKey());

        InventoryReservation::create([
            'inventory_id' => $inventory->id,
            'product_sku' => $event->orderProduct->sku_ordered,
            'warehouse_code' => $inventory->warehouse_code,
            'quantity_reserved' => $event->orderProduct->quantity_to_ship,
            'comment' => 'Order #' . $event->orderProduct->order->order_number,
            'custom_uuid' => $uuid,
        ]);

        ReservationsService::recalculateTotalQuantityReserved([$inventory->id]);
    }
}
