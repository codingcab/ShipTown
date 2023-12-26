<?php

namespace Tests\Feature\Pdf\Orders\OrderNumber;

use App\Models\Order;
use App\User;
use Tests\TestCase;

/**
 *
 */
class TemplateTest extends TestCase
{
    /**
     * @var string
     */
    protected string $uri = 'this set in setUp() method';

    /**
     * @var User
     */
    protected User $user;

    /**
     * @var Order
     */
    protected Order $order;

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->order = Order::factory()->create();

        $this->uri = '/pdf/orders/'.$this->order->order_number. '/address_label';
    }

    /** @test */
    public function test_if_uri_set()
    {
        $this->assertNotEmpty($this->uri);
    }

    /** @test */
    public function test_guest_call()
    {
        $response = $this->get($this->uri);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_user_call()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }

    /** @test */
    public function test_admin_call()
    {
        $this->user->assignRole('admin');

        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        $response->assertSuccessful();
    }
}
