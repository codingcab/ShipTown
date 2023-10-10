<?php

namespace Tests\Feature\Http\Controllers\Api\Modules\MagentoApi\MagentoApiConnectionController;

use App\Models\Warehouse;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Services\Magento2Integration;
use App\User;
use Spatie\Tags\Tag;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function test_success_call()
    {
        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $connection = MagentoConnection::create([
            'integration_class' => Magento2Integration::class,
            'base_url' => 'https://magento2.test',
            'magento_store_code' => 'default',
            'magento_inventory_source_code' => 'default',
            'magento_store_id' => 123456,
            'pricing_source_warehouse_id' => 1,
            'access_token_encrypted' => 'some-token',
            'api_access_token' => 'some-token',
        ]);

        $warehouse = Warehouse::query()->firstOrCreate(['code' => 'DUB'], ['name' => 'Dublin']);

        $warehouse->attachTag('source_dublin');

        $tag = Tag::findOrCreate('source_dublin');

        $response = $this->actingAs($user, 'api')
            ->json('put', route('api.modules.magento-api.connections.update', $connection), [
                'base_url'                          => 'https://magento2.test',
                'magento_store_code'                => 'default',
                'magento_inventory_source_code'     => 'default',
                'magento_store_id'                  => 123456,
                'tag'                               => 'some-tag',
                'inventory_source_warehouse_tag_id' => $tag->getKey(),
                'pricing_source_warehouse_id'       => $warehouse->id,
                'api_access_token'                  => 'some-token',
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
            'integration_class' => Magento2Integration::class,
            'base_url' => 'https://magento2.test',
            'magento_store_id' => 123456,
            'magento_store_code' => 'default',
            'magento_inventory_source_code'  => 'default',
            'pricing_source_warehouse_id' => 1,
        ]);

        $response = $this->actingAs($user, 'api')
            ->json('put', route('api.modules.magento-api.connections.update', $connection), []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'api_access_token',
        ]);
    }
}
