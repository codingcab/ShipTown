<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use App\Services\OrderService;

class ProcessImportedOrdersJob extends UniqueJob
{
    public function handle(): void
    {
        Api2cartOrderImports::query()
            ->whereNull('when_processed')
            ->orderBy('id')
            ->chunk(10, function ($api2cartOrderImports) {
                foreach ($api2cartOrderImports as $api2cartOrderImport) {
                    $this->processOrder($api2cartOrderImport);
                }
                usleep(10000); // 100ms
            });
    }

    private function processOrder(Api2cartOrderImports $orderImport): void
    {
        $data = $orderImport->raw_import;

        $uuid = implode('-', ['module-api2cart-connection-id', $orderImport->connection_id, 'remote-record-id', data_get($data, 'order_id')]);

        $orderAttributes = [
            'custom_unique_reference_id' => $uuid,
            'order_number'               => data_get($data, 'id'),
            'total'                      => data_get($data, 'totals.total', 0),
            'total_products'             => data_get($data, 'totals.subtotal'),
            'total_shipping'             => data_get($data, 'totals.shipping', 0),
            'total_discounts'            => data_get($data, 'totals.discount', 0),
            'total_paid'                 => data_get($data, 'total.total_paid', 0),
            'shipping_method_name'       => data_get($data, 'shipping_method.name', 0),
            'shipping_method_code'       => data_get($data, 'shipping_method.additional_fields.code', 0),
            'order_placed_at'            => $orderImport->ordersCreateAt()->tz('UTC'),
            'order_products'             => $orderImport->extractOrderProducts(),
            'shipping_address'           => $orderImport->extractShippingAddressAttributes(),
            'raw_import'                 => $data,
        ];

        $order = OrderService::updateOrCreate($orderAttributes);

        $orderImport->update([
            'order_number' => $order->order_number,
            'when_processed' => now(),
        ]);
    }
}
