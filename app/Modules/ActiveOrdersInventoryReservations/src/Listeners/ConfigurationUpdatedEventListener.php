<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Listeners;

use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\ActiveOrdersInventoryReservations\src\ActiveOrdersInventoryReservationsServiceProvider;
use App\Modules\ActiveOrdersInventoryReservations\src\Events\ConfigurationUpdatedEvent;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;

class ConfigurationUpdatedEventListener
{
    public function handle(ConfigurationUpdatedEvent $event): void
    {
        /** @var Configuration $config */
        $config = Configuration::query()->firstOrCreate();

        if ($config->warehouse_id === null) {
            ActiveOrdersInventoryReservationsServiceProvider::disableModule();
            return;
        }

        InventoryReservation::query()
            ->where('custom_uuid', 'like', ReservationsService::UUID_PREFIX . '%')
            ->get()
            ->each(fn(InventoryReservation $reservation) => $reservation->delete());

        OrderProduct::query()->whereIn('order_id', Order::query()->where('is_active', true)->pluck('id'))
            ->get()
            ->each(function (OrderProduct $orderProduct) use ($config) {
                if ($orderProduct->product_id === null) {
                    return true;
                }

                $inventory = Inventory::find($orderProduct->product_id, $config->warehouse_id);

                InventoryReservation::create([
                    'inventory_id' => $inventory->id,
                    'product_sku' => $orderProduct->product->sku,
                    'warehouse_code' => $inventory->warehouse_code,
                    'quantity_reserved' => $orderProduct->quantity_to_ship,
                    'comment' => 'Order #' . $orderProduct->order->order_number,
                    'custom_uuid' => ReservationsService::getUuid($orderProduct),
                ]);

                return true;
            });
    }
}
