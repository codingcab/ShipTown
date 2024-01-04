<?php

namespace Tests\Feature\Api\Modules\InventoryReservation\Configuration;

use App\Models\Warehouse;
use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use App\Modules\InventoryReservations\src\Models\Configuration;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function test_update_call_returns_ok()
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $warehouse = Warehouse::firstOrCreate(['code' => '999'], ['name' => '999']);
        $params = [
            'warehouse_id' => $warehouse->id,
        ];
        $inventoryReservationsConfiguration = Configuration::first();

        $response = $this->actingAs($user, 'api')->put(route('api.modules.inventory-reservations.configuration.update', $inventoryReservationsConfiguration), $params);

        $response->assertStatus(200);
    }
}
