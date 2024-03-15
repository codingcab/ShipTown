<?php

namespace App\Modules\OrderStatus\src\Listeners;

use App\Events\Order\OrderUpdatedEvent;
use App\Models\OrderStatus;

class OrderUpdatedEventListener
{
    public function handle(OrderUpdatedEvent $event)
    {
        if ($event->order->isAttributeNotChanged('status_code')) {
            return;
        }

        $orderStatus = OrderStatus::query()->firstOrCreate(['code' => $event->order->status_code], ['name' => $event->order->status_code]);

        if ($event->order->is_active != $orderStatus->order_active) {
            $event->order->is_active = $orderStatus->order_active;
            $event->order->save();
        }

        if ($event->order->is_on_hold != $orderStatus->order_on_hold) {
            $event->order->is_on_hold = $orderStatus->order_on_hold;
            $event->order->save();
        }
    }
}
