<?php

namespace Tests\Feature\Http\Controllers\Api\Modules\DpdUk\DpdUkConnectionController;

use App\Modules\DpdUk\src\Models\Connection;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_index_call_returns_ok()
    {
        factory(Connection::class)->create();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')
            ->getJson(route('api.modules.dpd-uk.dpd-uk-connections.index'));

        $response->assertOk();

        $this->assertEquals(1, $response->json('meta.total'), 'No records returned');

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'id',
                    "account_number",
                    "username",
                    "password",
                    "collection_address_id",
                    "geo_session",
                    "collection_address" => [],
                    "updated_at",
                    "created_at",
                ],
            ],
        ]);
    }
}