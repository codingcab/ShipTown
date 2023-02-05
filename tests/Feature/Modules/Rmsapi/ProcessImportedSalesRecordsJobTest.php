<?php

namespace Tests\Feature\Modules\Rmsapi;

use App\Models\Product;
use App\Modules\Rmsapi\src\Jobs\ProcessImportedSalesRecordsJob;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use App\Modules\Rmsapi\src\Models\RmsapiSaleImport;
use Tests\TestCase;

class ProcessImportedSalesRecordsJobTest extends TestCase
{
    public function testIfImportsSale()
    {
        // prepare
        /** @var RmsapiSaleImport $saleRecord */
        $saleRecord = RmsapiSaleImport::factory()->create();

        // execute
        ProcessImportedSalesRecordsJob::dispatchSync($saleRecord->rmsapiConnection->id);

        // assert
        $product = Product::findBySKU($saleRecord->sku);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->getKey(),
            'description' => 'rms_sale',
            'quantity_delta' => $saleRecord->quantity * -1,
        ]);

        $this->assertDatabaseMissing('modules_rmsapi_sales_imports', [
            'id' => $saleRecord->id,
            'processed_at' => null,
        ]);
    }

    public function testIfSkipsOrderProductShipmentsTransactions()
    {
        /** @var RmsapiConnection $rmsapiConnection */
        $rmsapiConnection = RmsapiConnection::factory()->create();

        /** @var RmsapiSaleImport $saleRecord */
        $saleRecord = RmsapiSaleImport::create([
            'connection_id' => $rmsapiConnection->id,
            'comment' => 'PM_OrderProductShipment_1234567890'
        ]);

        ProcessImportedSalesRecordsJob::dispatchSync($rmsapiConnection->id);

        $this->assertDatabaseMissing('modules_rmsapi_sales_imports', [
            'id' => $saleRecord->id,
            'processed_at' => null,
        ]);
    }
}
