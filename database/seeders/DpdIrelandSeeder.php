<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Modules\DpdIreland\src\DpdIrelandServiceProvider;
use App\Modules\DpdIreland\src\Models\DpdIreland;
use Illuminate\Database\Seeder;

class DpdIrelandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(empty(env('TEST_DPD_USER'))) {
            return;
        }

        DpdIreland::factory()->create([
            'token'             => env('TEST_DPD_TOKEN'),
            'user'              => env('TEST_DPD_USER'),
            'password'          => env('TEST_DPD_PASSWORD'),
        ]);

        DpdIrelandServiceProvider::enableModule();

        OrderStatus::factory()->create([
            'name' => 'test_orders_courier_dpd_ireland',
            'code' => 'test_orders_courier_dpd_ireland',
            'order_active' => true,
            'order_on_hold' => true,
        ]);

        $this->createTestOrder();
    }

    /**
     * @return void
     */
    private function createTestOrder(): void
    {
        /** @var OrderAddress $testAddress */
        $testAddress = OrderAddress::factory()->make();
        $testAddress->first_name = 'John';
        $testAddress->last_name = 'Smith';
        $testAddress->phone = '12345678901';
        $testAddress->company = "DPD Group Ltd";
        $testAddress->country_code = "IE";
        $testAddress->postcode = "B661BY";
        $testAddress->address1 = "DPD Ireland";
        $testAddress->address2 = "Unit 2B Midland Gateway Bus";
        $testAddress->city = "Westmeath";
        $testAddress->state_code = "Kilbeggan";
        $testAddress->email = 'john.smith@dpd.ie';
        $testAddress->save();

        /** @var Order $order */
        $order = Order::factory()->make([
            'status_code' => 'test_orders_courier_dpd_ireland',
            'label_template' => 'dpd_irl_next_day',
        ]);
        $order->shippingAddress()->associate($testAddress);
        $order->save();

        OrderProduct::factory()->count(3)->create(['order_id' => $order->getKey()]);

        $order->refresh();

        $order->update(['total_paid' => $order->total_order]);
    }
}
