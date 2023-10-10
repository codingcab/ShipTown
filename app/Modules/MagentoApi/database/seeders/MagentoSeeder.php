<?php

namespace App\Modules\MagentoApi\database\seeders;

use App\Models\Tag;
use App\Models\Warehouse;
use App\Modules\MagentoApi\src\EventServiceProviderBase;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use Illuminate\Database\Seeder;

class MagentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('TEST_MODULES_MAGENTO_BASE_URL') === null) {
            return;
        }

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::query()->firstOrCreate(['code' => '999'], ['name' => 'Web Orders']);
        $warehouse->attachTag('Magento Stock');

        /** @var Warehouse $warehouse */
        $warehouseDublin = Warehouse::query()->firstOrCreate(['code' => 'DUB'], ['name' => 'Dublin']);
        $warehouseDublin->attachTag('Magento Stock');

        MagentoConnection::create([
            'is_enabled' => false,
            'integration_class' => env('TEST_MODULES_INTEGRATION_CLASS'),
            'base_url' => env('TEST_MODULES_MAGENTO_BASE_URL'),
            'inventory_totals_tag_id' => Tag::findFromString('Magento Stock')->getKey(),
            'pricing_source_warehouse_id' => $warehouseDublin->getKey(),
            'api_access_token' => env('TEST_MODULES_MAGENTO_ACCESS_TOKEN'),
        ]);

        EventServiceProviderBase::enableModule();
    }
}
