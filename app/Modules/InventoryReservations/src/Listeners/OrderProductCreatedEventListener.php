<?php

namespace App\Modules\InventoryReservations\src\Listeners;

use App\Events\OrderProduct\OrderProductCreatedEvent;
use App\Models\Inventory;
use App\Modules\InventoryReservations\src\Jobs\UpdateInventoryQuantityReservedJob;
use App\Modules\InventoryReservations\src\Models\Configuration;
use App\Modules\InventoryReservations\src\Models\InventoryReservation;
use App\Modules\InventoryReservations\src\Services\ReservationsService;

class OrderProductCreatedEventListener
{
    /**
     * Handle the event.
     *
     * @param OrderProductCreatedEvent $event
     *
     * @return void
     */
    public function handle(OrderProductCreatedEvent $event)
    {
        $config = Configuration::first();

        $inventory = Inventory::where('product_id', $event->orderProduct->product_id)
            ->where('warehouse_id', $config->warehouse_id)
            ->first(['id', 'warehouse_code', 'quantity_reserved']);

        $inventoryReservationUuid = implode('_', ['order_product_id', $event->orderProduct->getKey()]);

        InventoryReservation::create([
            'inventory_id' => $inventory->id,
            'product_sku' => $event->orderProduct->sku_ordered,
            'warehouse_code' => $inventory->warehouse_code,
            'quantity_reserved' => $event->orderProduct->quantity_to_ship,
            'comment' => 'Order #' . $event->orderProduct->order->order_number, // Order #1
            'custom_uuid' => $inventoryReservationUuid, // 123
        ]);

        ReservationsService::recalculateTotalQuantityReserved($inventory->id);

        // todo - in what circumstances would product_id be null?
        if ($event->orderProduct->product_id === null) {
            return;
        }

        UpdateInventoryQuantityReservedJob::dispatchSync($event->orderProduct->product_id);
    }
}
