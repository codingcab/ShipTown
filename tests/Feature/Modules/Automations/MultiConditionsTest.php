<?php

namespace Tests\Feature\Modules\Automations;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\LineCountEqualsCondition;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Tests\TestCase;

class MultiConditionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create();

        /** @var Automation $automation1 */
        $automation1 = Automation::create([
            'enabled' => true,
            'name' => 'status1 to status2',
        ]);

        Condition::create([
            'automation_id' => $automation1->getKey(),
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => $orderProduct->order->status_code,
        ]);

        Condition::create([
            'automation_id' => $automation1->getKey(),
            'condition_class' => LineCountEqualsCondition::class,
            'condition_value' => '1'
        ]);

        Action::create([
            'automation_id' => $automation1->getKey(),
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'new_status'
        ]);
    }

    public function testExample()
    {
        RunEnabledAutomationsJob::dispatchNow();

        $this->assertCount(1, Order::query()->where(['status_code' => 'new_status'])->get());

        $this->assertTrue(true);
    }
}
