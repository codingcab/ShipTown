<?php

namespace App\Widgets;

use App\Models\Order;
use App\Models\OrderStatus;
use Arrilot\Widgets\AbstractWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActiveOrdersWidget extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $order_status_counts = Order::query()
            ->select([
                'orders.status_code',
                DB::raw('count(*) as order_count')
            ])
            ->where(['is_active' => true])
            ->whereNotIn('status_code', OrderStatus::getToFollowStatusList())
            ->groupBy(['status_code'])
            ->orderBy('status_code')
            ->get();

        $total_count = 0;

        foreach ($order_status_counts as $order_status) {
            $total_count += $order_status['order_count'];
        }

        return view('widgets.active_orders_widget', [
            'config' => $this->config,
            'order_status_counts' => $order_status_counts,
            'total_count' => $total_count,
        ]);
    }
}
