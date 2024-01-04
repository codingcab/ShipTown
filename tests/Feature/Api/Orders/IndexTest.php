<?php

namespace Tests\Feature\Api\Orders;

use App\Models\Order;
use App\User;
use Laravel\Passport\Passport;
use Spatie\Tags\Tag;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /**
     * @return void
     */
    public function test_has_tags_filter_exists()
    {
        Passport::actingAs(
            User::factory()->create()
        );

        Order::query()->forceDelete();
        Tag::query()->forceDelete();

        $order = Order::factory()->create();

        $order->attachTag('Test');

        $response = $this->json('GET', implode(',', [
            '/api/orders?filter[has_tags]=Test&include=activities',
            'activities.causer,shipping_address',
            'order_shipments,order_products,order_products.product',
            'order_products.product.aliases,packer,order_comments,order_comments.user'
        ]));

        ray($response->getContent());

        $response->assertSuccessful();

        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_has_tags_filter_missing()
    {
        Passport::actingAs(
            User::factory()->create()
        );

        Order::query()->forceDelete();
        Tag::query()->forceDelete();

        $order = Order::factory()->create();

        $response = $this->get('api/orders?filter[has_tags]=Test');

        $this->assertCount(0, $response->json('data'));
    }

    /** @test */
    public function test_index_call_returns_ok()
    {
        Order::query()->forceDelete();
        Order::factory()->create();

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson(route('api.orders.index'));

        $response->assertOk();

        $this->assertCount(1, $response->json('data'));

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'id',
                    'status_code',
                ],
            ],
        ]);
    }
}
