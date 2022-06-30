<?php

namespace App\Modules\Webhooks\src\Jobs;

use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Modules\Webhooks\src\Models\PendingWebhook;
use App\Modules\Webhooks\src\Services\SnsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use romanzipp\QueueMonitor\Traits\IsMonitored;

/**
 * Class PublishOrdersWebhooksJob.
 */
class PublishInventoryWebhooksJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use IsMonitored;

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $query = PendingWebhook::query()
            ->where([
                'model_class' => Inventory::class,
                'reserved_at' => null
            ])
            ->orderBy('id')
            ->limit(10);

        $chunk = $query->get();

        while ($chunk->isNotEmpty()) {
            $pendingWebhookIds = $chunk->pluck('id');

            try {
                PendingWebhook::query()->whereIn('id', $pendingWebhookIds)->update(['reserved_at' => now()]);

                $this->publishInventoryMessage($chunk);

                PendingWebhook::query()->whereIn('id', $pendingWebhookIds)->delete();
            } catch (Exception $exception) {
                PendingWebhook::query()
                    ->whereIn('id', $pendingWebhookIds)
                    ->update(['reserved_at' => null]);

                throw $exception;
            }

            $chunk = $query->get();
        }
    }

    /**
     * @param $chunk
     */
    private function publishInventoryMessage($chunk): void
    {
        $inventoryCollection = InventoryResource::collection(
            Inventory::query()
                ->whereIn('id', $chunk->pluck('model_id'))
                ->orderBy('id')
                ->with(['product', 'warehouse'])
                ->get()
        );

        $payload = collect(['Inventory' => $inventoryCollection]);

        SnsService::publishNew($payload->toJson());
    }
}