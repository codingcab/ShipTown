<?php

namespace Database\Factories;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\Product;
use Closure;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataCollectionRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'data_collection_id' =>  function () {
                return DataCollection::factory()->create();
            },
            'product_id' => function () {
                return Product::factory()->create();
            },
            'quantity_requested' => rand(1, 100),
            'quantity_scanned' => rand(1, 100),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (DataCollectionRecord $dataCollectionRecord) {
            $dataCollectionRecord->update([
                'inventory_id' => Inventory::where([
                    'product_id' => $dataCollectionRecord->product_id,
                    'warehouse_id' => $dataCollectionRecord->dataCollection->warehouse_id,
                ])->first()->id,
            ]);
        });
    }
}
