<?php

namespace Tests;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use JMac\Testing\Traits\AdditionalAssertions;
use Spatie\Tags\Tag;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use AdditionalAssertions;

    protected function setUp(): void
    {
        parent::setUp();

        ray()->clearAll();
        ray()->className($this)->blue();

        Product::query()->forceDelete();
        Inventory::query()->forceDelete();
        ProductAlias::query()->forceDelete();
        OrderProduct::query()->forceDelete();
        Order::query()->forceDelete();
        OrderStatus::query()->forceDelete();
        Warehouse::query()->forceDelete();
        Tag::query()->forceDelete();
    }
}
