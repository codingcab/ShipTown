<?php

namespace Tests\Feature\Api\Run\Hourly\Jobs;

use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_returns_ok()
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson(route('run.hourly.jobs.index'));

        $response->assertOk();
    }
}
