<?php

namespace App\Modules\DataCollector\src\Jobs;

use App\Models\DataCollection;
use App\Models\DataCollectionTransferIn;
use App\Models\DataCollectionTransferOut;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class SyncCheckFailedProductsJob.
 */
class DispatchCollectionsTasksJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle()
    {
        Log::debug('EnsureCorrectlyArchived job started');

        DataCollection::query()->where(['currently_running_task' => DataCollectionTransferIn::class])
            ->update(['currently_running_task' => TransferInJob::class]);

        DataCollection::query()->where(['currently_running_task' => DataCollectionTransferOut::class])
            ->update(['currently_running_task' => TransferOutJob::class]);

        DataCollection::withTrashed()
            ->whereNotNull('currently_running_task')
            ->chunkById(1, function ($batch) {
                $batch->each(function (DataCollection $dataCollection) {
                    try {
                        /** @var Dispatchable $job */
                        $job = $dataCollection->currently_running_task;
                        $job::dispatch($dataCollection->getKey());
                    } catch (Exception $e) {
                        Log::error($e->getMessage());
                        report($e);
                    }
                });
            });

        Log::info('EnsureCorrectlyArchived job finished');
    }
}
