<?php

namespace App\Modules\Magento2MSI\src\Listeners;

use App\Events\Product\ProductTagAttachedEvent;
use App\Modules\Magento2MSI\src\Models\MagentoConnection;
use App\Modules\Magento2MSI\src\Models\MagentoProduct;

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
                    MagentoProduct::firstOrCreate([
                        'connection_id' => $connection->getKey(),
                        'product_id' => $event->product->id
                    ], []);
                });
        }
    }
}
