<?php

namespace Tests\Feature\Http\Controllers\Api\TagController;

use App\Models\Warehouse;
use App\User;
use Spatie\Tags\Tag;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = 'api/tags';

    /** @test */
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create()->assignRole('admin');

        Tag::findOrCreate('some-tag');

        $response = $this->actingAs($user, 'api')->getJson($this->uri, []);


        ray(Tag::all()->toArray());
        ray($response->json());

        $response->assertSuccessful();

        $this->assertCount(1, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id'
                ],
            ],
        ]);
    }

    /** @test */
    public function testUserAccess()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->getJson($this->uri, []);

        $response->assertSuccessful();
    }
}
