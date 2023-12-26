<?php

namespace Tests\Feature\Api\Run\Daily\Jobs;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_returns_ok()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson(route('run.daily.jobs.index'));

        $response->assertOk();
    }
}
