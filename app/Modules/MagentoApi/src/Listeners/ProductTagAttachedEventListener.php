<?php

namespace App\Modules\MagentoApi\src\Listeners;

use App\Events\Product\ProductTagAttachedEvent;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;

class ProductTagAttachedEventListener
{
    /**
     * Handle the event.
     *
     * @param ProductTagAttachedEvent $event
     *
     * @return void
     */
    public function handle(ProductTagAttachedEvent $event)
    {
        if ($event->tag === 'Available Online') {
            MagentoConnection::query()
                ->get()
                ->each(function (MagentoConnection $connection) use ($event) {
                    MagentoProduct::query()->firstOrCreate([
                        'connection_id' => $connection->getKey(),
                        'product_id' => $event->product->id
                    ], []);
                });
        }
    }
}
