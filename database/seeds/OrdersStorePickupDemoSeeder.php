<?php

use App\Events\Order\ActiveOrderCheckEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Models\NavigationMenu;
use App\Models\Order;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeEqualsCondition;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Illuminate\Database\Seeder;

class OrdersStorePickupDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = [
            [
                'name' => 'Status: store_pickup',
                'url' => '/autopilot/packlist?inventory_source_location_id=1&status=store_pickup&address_label_template=address_label',
                'group' => 'packlist'
            ]
        ];

        NavigationMenu::insert($menu);

        factory(Order::class, 10)
            ->with('orderProducts', 4)
            ->create(['status_code' => 'store_pickup', 'shipping_method_code' => 'store_pickup']);

        Order::all()->each(function (Order $order) {
            $order->total_paid = $order->total;
            $order->save();
        });
    }
}