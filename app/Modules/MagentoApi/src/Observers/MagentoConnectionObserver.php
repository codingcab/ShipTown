<?php

namespace App\Modules\MagentoApi\src\Observers;

use App\Modules\MagentoApi\src\Jobs\Maintenance\EnsureProductRecordsExistJob;
use App\Modules\MagentoApi\src\Jobs\Maintenance\FillForeignIndexesJob;
use App\Modules\MagentoApi\src\Models\MagentoConfiguration;

class MagentoConnectionObserver
{
    public function created()
    {
        MagentoConfiguration::query()->update(['last_product_id_checked' => 0]);

        EnsureProductRecordsExistJob::dispatch();

        FillForeignIndexesJob::dispatch();
    }
}
