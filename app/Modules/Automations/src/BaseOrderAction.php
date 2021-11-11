<?php

namespace App\Modules\Automations\src;

use App\Events\Order\OrderCreatedEvent;
use App\Events\Order\OrderUpdatedEvent;
use App\Models\Order;
use Log;

/**
 *
 */
abstract class BaseOrderAction
{
    /**
    * @var OrderCreatedEvent|OrderUpdatedEvent
    */
    private $event;

    /**
     * @var Order
     */
    public Order $order;

    public function __construct($event)
    {
        $this->event = $event;
        $this->order = $event->order;
    }

    /**
     * @param $options
     */
    public function handle($options)
    {
        Log::debug('automation.action.executing', [
            'order_number' => $this->event->order->order_number,
            'class' => class_basename(self::class),
            '$options' => $options,
        ]);
    }
}
