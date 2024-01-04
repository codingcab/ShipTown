<?php

namespace Tests\Feature\Api\Run\Sync;

use App\Events\SyncRequestedEvent;
use App\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class IndexTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('user');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_index_call_returns_ok()
    {
        Event::fake();

        $response = $this->get('api/run/sync');

        $response->assertSuccessful();

        Event::assertDispatched(SyncRequestedEvent::class);
    }
}
