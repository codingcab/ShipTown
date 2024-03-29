<?php

namespace Database\Seeders\Demo\DataCollections;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransferIn;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\Jobs\DispatchCollectionsTasksJob;
use App\Modules\DataCollector\src\Jobs\TransferInJob;
use Illuminate\Database\Seeder;

class ArchivedTransfersFromWarehouseSeeder extends Seeder
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

                DataCollectionRecord::factory()
                    ->count(5)
                    ->create([
                        'data_collection_id' => $dataCollection->getKey(),
                    ]);

                $dataCollection->delete();

                TransferInJob::dispatch($dataCollection->getKey());
            });
    }
}
