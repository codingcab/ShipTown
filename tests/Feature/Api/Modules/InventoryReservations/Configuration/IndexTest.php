<?php

namespace Tests\Feature\Api\Modules\InventoryReservation\Configuration;

use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_index_call_returns_ok()
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson(route('api.modules.inventory-reservations.configuration.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'warehouse_id'
            ],
        ]);
    }
}
