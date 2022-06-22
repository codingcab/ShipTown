<?php

namespace App\Modules\Automations\src\Abstracts;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * @property-read Order $order
 */
abstract class BaseOrderActionAbstract
{
    /**
     * @var Order
     */
    public Order $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * @param string $options
     * @return bool
     */
    public function handle(string $options = ''): bool
    {
        Log::debug('automation.action.executing', [
            'order_number' => $this->order->order_number,
            'class' => class_basename(self::class),
            '$options' => $options,
        ]);

        return true;
    }
}
