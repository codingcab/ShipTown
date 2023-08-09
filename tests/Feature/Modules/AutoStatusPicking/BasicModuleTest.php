<?php

namespace Tests\Feature\Modules\AutoStatusPicking;

use App\Jobs\DispatchEveryDayEventJob;
use App\Modules\AutoStatusPicking\src\AutoStatusPickingServiceProvider;
use App\Modules\AutoStatusPicking\src\Jobs\RefillPickingIfEmptyJob;
use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_basic_functionality()
    {
        AutoStatusPickingServiceProvider::enableModule();
        InventoryReservationsEventServiceProviderBase::enableModule();

        Bus::fake();

        DispatchEveryDayEventJob::dispatch();

        Bus::assertDispatched(RefillPickingIfEmptyJob::class);
    }
}
