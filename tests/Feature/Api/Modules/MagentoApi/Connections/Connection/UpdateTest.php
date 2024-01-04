<?php

namespace Tests\Feature\Api\Modules\MagentoApi\Connections\Connection;

use App\Models\Warehouse;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_success_config_create()
    {
        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $connection = MagentoConnection::create([
            'base_url' => 'https://magento2.test',
            'magento_store_id' => 123456,
            'pricing_source_warehouse_id' => 1,
            'access_token_encrypted' => 'some-token',
        ]);

        $warehouse = Warehouse::firstOrCreate(['code' => '999'], ['name' => '999']);

        $response = $this->actingAs($user, 'api')
            ->json('put', route('api.modules.magento-api.connections.update', $connection), [
                'base_url'                          => 'https://magento2.test',
                'magento_store_id'                  => 123456,
                'tag'                               => 'some-tag',
                'pricing_source_warehouse_id'       => $warehouse->id,
                'access_token_encrypted'            => 'some-token',
            ]);

        $response->assertSuccessful();
    }

    /** @test */
    public function test_failing_config_create()
    {
        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $connection = MagentoConnection::create([
            'base_url' => 'https://magento2.test',
            'magento_store_id' => 123456,
            'pricing_source_warehouse_id' => 1,
            'access_token_encrypted' => 'some-token',
        ]);

        $response = $this->actingAs($user, 'api')
            ->json('put', route('api.modules.magento-api.connections.update', $connection), []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'base_url',
            'magento_store_id',
            // 'tag',
            // 'pricing_source_warehouse_id',
            'access_token_encrypted',
        ]);
    }
}
