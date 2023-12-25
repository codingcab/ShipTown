<?php

namespace Tests\Feature\Modules\Magento2MSI;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ModuleServiceProvider::enableModule();
    }

    /** @test */
    public function testBasicFunctionality()
    {
        $this->fail('This test has not been implemented yet.');
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
}
