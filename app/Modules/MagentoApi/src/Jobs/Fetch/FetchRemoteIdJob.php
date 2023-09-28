<?php

namespace App\Modules\MagentoApi\src\Jobs\Fetch;

use App\Abstracts\UniqueJob;

class FetchRemoteIdJob extends UniqueJob
{
    public function handle()
    {
        FetchStockItemsJob::dispatchSync();
    }
}
