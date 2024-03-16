<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Listeners;

use App\Events\Order\OrderUpdatedEvent;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\OrderProduct;
use App\Modules\ActiveOrdersInventoryReservations\src\ActiveOrdersInventoryReservationsServiceProvider;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;

class OrderUpdatedEventListener
{
    public function handle(OrderUpdatedEvent $event): bool
    {
        if ($event->order->isAttributeNotChanged('is_active')) {
            return true;
        }

        if ($event->order->refresh()->is_active) {
            $this->createInventoryReservationsForOrderProducts($event);
        } else {
            $this->deleteInventoryReservationsForOrderProducts($event);
        }

        return true;
    }

    public function createInventoryReservationsForOrderProducts(OrderUpdatedEvent $event): void
    {
        /** @var Configuration $config */
        $config = Configuration::query()->firstOrCreate();

        if ($config->warehouse_id === null) {
            ActiveOrdersInventoryReservationsServiceProvider::disableModule();
            return;
        }

        $inventoryReservations = $event->order
            ->orderProducts()
            ->whereNotNull('product_id')
            ->map(function (OrderProduct $orderProduct) use ($event, $config) {
                $inventory = Inventory::find($orderProduct->product_id, $config->warehouse_id);

                return [
                    'inventory_id' => $inventory->id,
                    'product_sku' => $orderProduct->sku_ordered,
                    'warehouse_code' => $inventory->warehouse_code,
                    'quantity_reserved' => $orderProduct->quantity_to_ship,
                    'comment' => 'Order #' . $event->order->order_number,
                    'custom_uuid' => ReservationsService::getUuid($orderProduct),
                ];
            });

        InventoryReservation::query()->create($inventoryReservations->toArray());
    }

    public function deleteInventoryReservationsForOrderProducts(OrderUpdatedEvent $event): void
    {
        $uuidPrefix = implode(';', [
            ReservationsService::UUID_PREFIX,
            'order_id_' . $event->order->getKey(),
        ]);

        InventoryReservation::query()
            ->where('custom_uuid', 'like', $uuidPrefix . ';%')
            ->get()
            ->each(fn(InventoryReservation $inventoryReservation) => $inventoryReservation->delete());
    }
}
