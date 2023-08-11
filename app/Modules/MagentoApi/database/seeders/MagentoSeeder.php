<?php

namespace App\Modules\MagentoApi\database\seeders;

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

        MagentoConnection::create([
            'base_url' => env('TEST_MODULES_MAGENTO_BASE_URL'),
            'api_access_token' => env('TEST_MODULES_MAGENTO_ACCESS_TOKEN'),
        ]);

        EventServiceProviderBase::enableModule();
    }
}
