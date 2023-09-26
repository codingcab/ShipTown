<?php

namespace Tests\Feature\Modules\MagentoApi;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Models\Product;
use App\Models\Tag;
use App\Models\Warehouse;
use App\Modules\MagentoApi\src\EventServiceProviderBase;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        EventServiceProviderBase::enableModule();
    }
    /** @test */
    public function test_module_basic_functionality()
    {
        if (env('TEST_MODULES_MAGENTO_BASE_URL') === null) {
            $this->markTestSkipped('Magento base url not set');
        }

        $this->assertTrue(true, 'Most basic test... to be continued');
    }

    /** @test */
    public function testIfNoErrorsDuringEvents()
    {
        EveryMinuteEvent::dispatch();
        EveryFiveMinutesEvent::dispatch();
        EveryTenMinutesEvent::dispatch();
        EveryHourEvent::dispatch();
        EveryDayEvent::dispatch();

        $this->assertTrue(true, 'Errors encountered while dispatching events');
    }

    public function test_if_fills_foreign_keys()
    {
        EventServiceProviderBase::enableModule();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();
        $warehouse->attachTag('online stock');

        /** @var Product $product */
        $product = Product::factory()->create();
        $product->attachTag('Available Online');

        /** @var Product $product */
        $product2 = Product::factory()->create();
        $product2->attachTag('Available Online');

        $tag = Tag::findFromString('online stock');

        MagentoConnection::factory()->create(['inventory_totals_tag_id' => $tag->getKey()]);

        $builder = MagentoProduct::query()
            ->whereNull('inventory_totals_by_warehouse_tag_id');

        ray($builder->get()->toArray());

        $this->assertTrue($builder->exists());

    }
}
