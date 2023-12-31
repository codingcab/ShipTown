<?php

namespace App\Modules\Maintenance\src\Jobs\temp;

use App\Models\Inventory;
use App\Modules\InventoryReservations\src\Models\Configuration;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StockInReservationWarehouseMonitorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        /** @var Configuration $config */
        $config = Configuration::query()->firstOrCreate();

        $inventoryReservationsWarehouseId = $config->warehouse_id;

        if (is_null($inventoryReservationsWarehouseId)) {
            return;
        }

        Inventory::query()
            ->where(['warehouse_id' => $inventoryReservationsWarehouseId])
            ->where('quantity', '>', '0')
            ->get()->each(function (Inventory $inventory) {
                $inventory->update(['quantity' => 0]);

                report(new Exception('Incorrect quantity, web reservations cannot have stock'));
            });
    }
}
