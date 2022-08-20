<?php

namespace Tests\Feature\Routes\Api;

use App\Jobs\SyncRequestJob;
use App\Modules\Rmsapi\src\Jobs\DispatchImportJobs;
use App\Modules\Rmsapi\src\Jobs\ImportProductsJob;
use App\Modules\Rmsapi\src\Jobs\ImportShippingsJob;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use App\Modules\Rmsapi\src\RmsapiModuleServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIfDispatchesJobs()
    {
        Bus::fake();

        RmsapiModuleServiceProvider::enableModule();

        factory(RmsapiConnection::class)->create();

        (new SyncRequestJob())->handle();

        Bus::assertDispatched(DispatchImportJobs::class);
    }
}
