<?php

namespace Tests\Feature\Api\Heartbeats;

use App\Models\Heartbeat;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function test_index_call_returns_ok()
    {
        // add expired heartbeat
        Heartbeat::updateOrCreate([
                'code' => 'somealert'
            ],
            [
                'expires_at' => now()->subMinutes(10),
                'error_message' => 'Some error message'
            ]
        );

        $response = $this->get(route('api.heartbeats.index'));
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'code',
                    'expired_at',
                    'error_message'
                ],
            ],
        ]);
        $response->assertJsonFragment(['code' => 'somealert']);
    }
}
