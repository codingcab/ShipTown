<?php

namespace App\Modules\MagentoApi\src\Listeners;

use App\Events\Product\ProductTagAttachedEvent;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;

class ProductTagAttachedEventListener
{
    public function handle(ProductTagAttachedEvent $event)
    {
        if ($event->tag !== 'Available Online') {
            return;
        }

        $collection = MagentoConnection::query()
            ->get('id')
            ->map(function (MagentoConnection $connection) use ($event) {
                return [
                    'connection_id' => $connection->getKey(),
                    'product_id' => $event->product->id
                ];
            });

        MagentoProduct::query()->insert($collection->toArray());
    }
}
