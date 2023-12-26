<?php

namespace Tests\Feature\Api\Modules\OrderAutomations\AutomationController;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_index_call_returns_ok()
    {
        $response = $this->get(route('api.modules.automations.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'enabled'
                ]
            ]
        ]);
    }
}
