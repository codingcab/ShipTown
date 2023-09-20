<?php

namespace App\Modules\OrderTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class EnsureAllRecordsExistsJob extends UniqueJob
{
    /**
     * @var string
     */
    private string $checkQuery = /** @lang text */
        '
        SELECT count(*) as count
        FROM orders
        LEFT JOIN orders_products_totals ON orders_products_totals.order_id = orders.id
        WHERE ISNULL(orders_products_totals.id)
        ';

    /**
     * @var string
     */
    private string $insertQuery = /** @lang text */
        'INSERT INTO orders_products_totals (order_id, created_at)
        SELECT
          orders.id as order_id,
          now()

        FROM orders
        LEFT JOIN orders_products_totals ON orders_products_totals.order_id = orders.id
        WHERE ISNULL(orders_products_totals.id)
        ';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        do {
            DB::statement($this->insertQuery);
        } while (data_get(DB::select($this->checkQuery), '0.count') > 0);
    }
}
