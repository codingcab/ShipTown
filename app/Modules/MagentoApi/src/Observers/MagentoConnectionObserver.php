<?php

namespace App\Modules\MagentoApi\src\Observers;

use App\Modules\MagentoApi\src\Jobs\EnsureProductRecordsExistJob;

class MagentoConnectionObserver
{
    public function created()
    {
        EnsureProductRecordsExistJob::dispatch();
    }
}
