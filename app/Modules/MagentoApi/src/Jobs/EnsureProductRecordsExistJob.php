<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Product;
use App\Modules\MagentoApi\src\Models\MagentoConfiguration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Tags\Tag;

class EnsureProductRecordsExistJob extends UniqueJob
{
    public function handle()
    {
        $tag = Tag::findOrCreate(['name' => 'Available Online'])->first();

        $configuration = MagentoConfiguration::query()->firstOrCreate();

        $minProductId = $configuration->last_product_id_checked ?? 0;
        $lastProductId = DB::table('products')->max('id') ?? 0;

        do {
            $maxProductId = min($minProductId + 10000, $lastProductId);

            $this->insertRecords($tag, $minProductId, $maxProductId);

            $configuration->update(['last_product_id_checked' => $maxProductId]);

            Log::debug('Job processing', [
                'job' => self::class,
                'minProductId' => $minProductId,
                'maxProductId' => $maxProductId,
                'lastProductId' => $lastProductId,
            ]);

            $minProductId = $maxProductId;
            usleep(400000); // 0.4 sec
        } while ($minProductId < $lastProductId);
    }

    private function insertRecords(Tag $tag, $minProductId, $maxProductId): void
    {
        DB::statement("
            INSERT INTO modules_magento2api_products (connection_id, product_id, created_at, updated_at)
            SELECT
                modules_magento2api_connections.id,
                taggables.taggable_id,
                now(),
                now()
            FROM products
            INNER JOIN taggables
                ON products.id = taggables.taggable_id
                AND taggables.taggable_type = ?
                AND taggables.tag_id = ?

            INNER JOIN modules_magento2api_connections
                ON modules_magento2api_connections.is_enabled = 1

            LEFT JOIN modules_magento2api_products
                ON modules_magento2api_products.product_id = taggables.taggable_id
                AND modules_magento2api_products.connection_id = modules_magento2api_connections.id

            WHERE
                  products.id BETWEEN ? AND ?
                AND modules_magento2api_products.product_id IS NULL
        ", [Product::class, $tag->getKey(), $minProductId, $maxProductId]);
    }
}
