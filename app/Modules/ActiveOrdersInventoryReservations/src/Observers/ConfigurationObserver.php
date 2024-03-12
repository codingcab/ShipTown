<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Observers;

use App\Events\Order\OrderUpdatedEvent;
use App\Events\OrderProduct\OrderProductCreatedEvent;
use App\Events\OrderProduct\OrderProductDeletedEvent;
use App\Events\OrderProduct\OrderProductUpdatedEvent;
use App\Models\OrderProduct;

class ConfigurationObserver
{
    public function updated(OrderProduct $orderProduct): void
    {
        $this->setOrdersPickedAtIfAllPicked($orderProduct);

        OrderProductUpdatedEvent::dispatch($orderProduct);

        // we do it here because touch() does not dispatch models UpdatedEvent
        OrderUpdatedEvent::dispatch($orderProduct->order);
    }
}
