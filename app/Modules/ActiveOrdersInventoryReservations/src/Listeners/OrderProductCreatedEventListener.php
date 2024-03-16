<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Listeners;

use App\Events\OrderProduct\OrderProductCreatedEvent;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Modules\ActiveOrdersInventoryReservations\src\ActiveOrdersInventoryReservationsServiceProvider;
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
        $config = Configuration::query()->firstOrCreate();

        if ($config->warehouse_id === null) {
            ActiveOrdersInventoryReservationsServiceProvider::disableModule();
            return;
        }

        $inventory = Inventory::find($event->orderProduct->product_id, $config->warehouse_id);

        InventoryReservation::create([
            'inventory_id' => $inventory->id,
            'product_sku' => $event->orderProduct->sku_ordered,
            'warehouse_code' => $inventory->warehouse_code,
            'quantity_reserved' => $event->orderProduct->quantity_to_ship,
            'comment' => 'Order #' . $event->orderProduct->order->order_number,
            'custom_uuid' => ReservationsService::getUuid($event->orderProduct),
        ]);
    }
}
