<?php

namespace Tests\Feature\Http\Controllers\Api\Modules\Rmsapi\RmsapiConnectionController;

use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_destroy_call_returns_ok()
    {
        $rmsApi = RmsapiConnection::factory()->create();

        $response = $this->delete(route('api.modules.rmsapi.connections.destroy', $rmsApi));
        $response->assertStatus(200);
    }
}
