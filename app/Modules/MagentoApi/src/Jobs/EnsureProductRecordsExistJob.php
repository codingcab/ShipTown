<?php

namespace App\Modules\MagentoApi\src\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Spatie\Tags\Tag;

/**
 * Class SyncCheckFailedProductsJob.
 */
class EnsureProductRecordsExistJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tag = Tag::findOrCreate(['name' => 'Available Online']);

        DB::statement("
            INSERT INTO modules_magento2api_products (connection_id, product_id, created_at, updated_at)
            SELECT
                modules_magento2api_connections.id,
                taggables.taggable_id,
                now(),
                now()
            FROM taggables
            INNER JOIN modules_magento2api_connections ON 1=1
            INNER JOIN products ON products.id = taggables.taggable_id
            WHERE taggables.tag_id = ?
            AND taggables.taggable_type = ?
            AND NOT EXISTS (
                SELECT 1 FROM modules_magento2api_products
                WHERE connection_id = modules_magento2api_connections.id
                  AND product_id = taggables.taggable_id
            )
        ", [$tag->first()->getKey(), \App\Models\Product::class]);
    }
}
