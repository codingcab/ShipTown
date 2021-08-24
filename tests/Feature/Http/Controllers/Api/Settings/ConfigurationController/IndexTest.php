<?php

namespace Tests\Feature\Http\Controllers\Api\Settings\ConfigurationController;

use App\Models\Configuration;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = factory(User::class)->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_index_call_returns_ok()
    {
        Configuration::create(['key' => 'test', 'value' => 'value']);
        Configuration::create(['key' => 'test2', 'value' => 'value']);
        $response = $this->call('GET', route('api.settings.configurations.index'), [
            'filterKeys' => [
                'test'
            ]
        ]);
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'test',
            ],
        ]);
    }
}
