<?php

namespace Database\Seeders\Demo\DataCollections;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransferIn;
use App\Models\Inventory;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class TransfersFromWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sourceWarehouse = Warehouse::query()->firstOrCreate(['code' => 'WHS'], ['name' => 'Warehouse']);

        Warehouse::query()
            ->whereNotIn('id', [$sourceWarehouse->id])
            ->get()
            ->each(function ($destinationWarehouse) use ($sourceWarehouse) {
                $dataCollection = DataCollection::factory()
                    ->create([
                        'warehouse_id' =>  $destinationWarehouse->getKey(),
                        'name' => 'Transfer from ' . $sourceWarehouse->name,
                        'type' => DataCollectionTransferIn::class,
                        'created_at' => now()->subHours(rand(24, 48)),
                    ]);

                // not scanned yet
                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'quantity_requested' => 1,
                    'quantity_scanned' => 0,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'quantity_requested' => 5,
                    'quantity_scanned' => 0,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'quantity_requested' => 54,
                    'quantity_scanned' => 0,
                ]);

                // fully scanned
                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'quantity_requested' => 6,
                    'quantity_scanned' => 6,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'quantity_requested' => 24,
                    'quantity_scanned' => 24,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'quantity_requested' => 1,
                    'quantity_scanned' => 1,
                ]);

                // over scanned
                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'quantity_requested' => 6,
                    'quantity_scanned' => 12,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'quantity_requested' => 24,
                    'quantity_scanned' => 25,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'quantity_requested' => 1,
                    'quantity_scanned' => 6,
                ]);

                // not requested
                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'quantity_requested' => null,
                    'quantity_scanned' => 12,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'quantity_requested' => null,
                    'quantity_scanned' => 8,
                ]);

                DataCollectionRecord::factory()->create([
                    'data_collection_id' => $dataCollection->getKey(),
                    'quantity_requested' => null,
                    'quantity_scanned' => 1,
                ]);
            });
    }
}
