<?php

namespace App\Modules\DataCollector\src\Services;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionStocktake;
use App\Models\DataCollectionTransferIn;
use App\Models\DataCollectionTransferOut;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\Jobs\TransferInJob;
use App\Modules\DataCollector\src\Jobs\TransferOutJob;
use App\Modules\DataCollector\src\Jobs\TransferToJob;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DataCollectorService
{
    public static function runAction(DataCollection $dataCollection, $action)
    {
        if ($action === 'transfer_in_scanned') {
            $dataCollection->update([
                'type' => DataCollectionTransferIn::class,
                'currently_running_task' => TransferInJob::class,
            ]);

            if (! $dataCollection->records()
                ->where('quantity_to_scan', '>', 0)
                ->exists()) {
                $dataCollection->delete();
            }

            TransferInJob::dispatch($dataCollection->id);
            return;
        }

        if ($action === 'transfer_out_scanned') {
            $dataCollection->update([
                'type' => DataCollectionTransferOut::class,
                'currently_running_task' => TransferOutJob::class
            ]);

            if (! $dataCollection->records()
                ->where('quantity_to_scan', '>', 0)
                ->exists()) {
                $dataCollection->delete();
            }

            TransferOutJob::dispatch($dataCollection->id);
            return;
        }

        if ($action === 'transfer_to_scanned') {
            $dataCollection->update([
                'type' => DataCollectionTransferOut::class,
                'currently_running_task' => TransferToJob::class
            ]);

            $dataCollection->delete();

            TransferToJob::dispatch($dataCollection->id);
            return;
        }

        if ($action === 'auto_scan_all_requested') {
            DB::transaction(function () use ($dataCollection) {
                $dataCollection->records()
                    ->whereNotNull('quantity_requested')
                    ->where('quantity_scanned', 0)
                    ->update(['quantity_scanned' => DB::raw('quantity_to_scan')]);
            });
            return;
        }

        if ($action === 'import_as_stocktake') {
            DB::transaction(function () use ($dataCollection) {
                self::importAsStocktake($dataCollection);
            });
        }
    }

    public static function transferScannedTo(DataCollection $sourceDataCollection, int $warehouse_id): DataCollection
    {
        $destinationDataCollection = $sourceDataCollection->destinationCollection;

        /** @var Warehouse $destinationWarehouse */
        $destinationWarehouse = Warehouse::findOrFail($warehouse_id);

        DB::transaction(function () use ($warehouse_id, $sourceDataCollection, &$destinationDataCollection, $destinationWarehouse) {
            // create collection
            if ($destinationDataCollection == null) {
                $name = implode(' ', ['Transfer from', $sourceDataCollection->warehouse->code, '-', $sourceDataCollection->name]);

                $destinationDataCollection = $sourceDataCollection->replicate(['destination_warehouse_id', 'currently_running_task', 'deleted_at']);
                $destinationDataCollection->type = DataCollectionTransferIn::class;
                $destinationDataCollection->warehouse_id = $warehouse_id;
                $destinationDataCollection->name = $name;
                $destinationDataCollection->save();

                $sourceDataCollection->update(['destination_collection_id' => $destinationDataCollection->id]);
            }

            $sourceDataCollection->update([
                'name' => implode(' ', ['Transfer To', $destinationWarehouse->code, '-', $sourceDataCollection->name]),
                'type' => DataCollectionTransferOut::class,
                'currently_running_task' => TransferOutJob::class,
            ]);

            $sourceDataCollection->delete();
        });

        $sourceDataCollection->records()
            ->where('quantity_scanned', '!=', DB::raw(0))
            ->get()
            ->each(function (DataCollectionRecord $record) use ($destinationDataCollection) {
                $inventory = Inventory::query()->firstOrCreate([
                    'product_id' => $record->product_id,
                    'warehouse_id' => $destinationDataCollection->warehouse_id,
                ]);

                $custom_uuid = implode('-', ['source_data_collections_records_id', $record->getKey()]);

                DataCollectionRecord::query()->firstOrCreate([
                    'custom_uuid' => $custom_uuid,
                ], [
                    'data_collection_id' => $destinationDataCollection->id,
                    'inventory_id' => $inventory->id,
                    'warehouse_id' => $inventory->warehouse_id,
                    'product_id' => $inventory->product_id,
                    'quantity_requested' => $record->quantity_scanned,
                ]);
            });

        TransferOutJob::dispatch($sourceDataCollection->getKey());

        return $destinationDataCollection;
    }

    public static function importAsStocktake(DataCollection $dataCollection)
    {
        $dataCollection->update(['type' => DataCollectionStocktake::class]);

        $dataCollection->delete();

        $dataCollection->records()
            ->where('quantity_scanned', '!=', DB::raw(0))
            ->get()
            ->each(function (DataCollectionRecord $record) use ($dataCollection) {
                /** @var Inventory $inventory */
                $inventory = Inventory::where([
                        'product_id' => $record->product_id,
                        'warehouse_id' => $dataCollection->warehouse_id,
                    ])
                    ->first();

                $quantityDelta = $record->quantity_scanned - $inventory->quantity;

                $custom_uuid = implode('-', ['source_data_collections_records_id', $record->getKey()]);

                InventoryMovement::query()->firstOrCreate([
                    'custom_uuid' => $custom_uuid,
                ], [
                    'inventory_id' => $inventory->id,
                    'type' => InventoryMovement::TYPE_STOCKTAKE,
                    'product_id' => $inventory->product_id,
                    'warehouse_id' => $inventory->warehouse_id,
                    'quantity_before' => $inventory->quantity,
                    'quantity_delta' => $quantityDelta,
                    'quantity_after' => $inventory->quantity + $quantityDelta,
                    'description' => 'stocktake',
                    'user_id' => Auth::id(),
                ]);
            });
    }

    public static function transferInRecord(DataCollectionRecord $record): void
    {
        DB::transaction(function () use ($record) {
            $record->refresh();

            if ($record->quantity_scanned == 0) {
                return;
            }

            $custom_unique_reference_id = implode(':', [
                'data_collection_id', $record->data_collection_id,
                'data_collection_record_id', $record->getKey(),
                $record->updated_at
            ]);

            $inventory = Inventory::query()->firstOrCreate([
                'warehouse_id' => $record->dataCollection->warehouse_id,
                'product_id' => $record->product_id
            ], []);


             InventoryService::adjust($inventory, $record->quantity_scanned, [
                 'description' => 'data collection transfer in',
                 'custom_unique_reference_id' => $custom_unique_reference_id
             ]);

            $record->update([
                'total_transferred_in' => $record->total_transferred_in + $record->quantity_scanned,
                'quantity_scanned' => 0
            ]);
        });
    }

    public static function transferOutRecord(DataCollectionRecord $record): void
    {
        DB::transaction(function () use ($record) {
            $record->refresh();

            if ($record->quantity_scanned == 0) {
                return;
            }

            $inventory = Inventory::query()->firstOrCreate([
                'warehouse_id' => $record->dataCollection->warehouse_id,
                'product_id' => $record->product_id
            ], []);

            $custom_unique_reference_id = implode(':', [
                'data_collection_id' , $record->data_collection_id,
                'data_collection_record_id' , $record->getKey(),
                $record->updated_at
            ]);

            InventoryService::adjust($inventory, $record->quantity_scanned * -1, [
                'description' => 'data collection transfer out',
                'custom_unique_reference_id' => $custom_unique_reference_id
            ]);

            $record->update([
                'total_transferred_out' => $record->total_transferred_out + $record->quantity_scanned,
                'quantity_scanned' => 0
            ]);
        });
    }
}
