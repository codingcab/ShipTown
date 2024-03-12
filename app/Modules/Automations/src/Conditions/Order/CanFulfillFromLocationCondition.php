<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;

/**
 *
 */
class CanFulfillFromLocationCondition extends BaseOrderConditionAbstract
{
    public function isValid(?string $expected_value = ''): bool
    {
        $warehouse_code = $expected_value;

        if ($warehouse_code === '0') {
            $warehouse_code = null;
        }

        $result = OrderService::canFulfill($this->order, $warehouse_code);

        Log::debug('Automation condition', [
            'order_number' => $this->order->order_number,
            'result' => $result,
            'class' => class_basename(self::class),
            'location_id' => $warehouse_code,
        ]);

        return $result;
    }
}
