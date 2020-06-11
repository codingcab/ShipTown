<?php

namespace App\Jobs\Rmsapi;

use App\Models\RmsapiConnection;
use App\Models\RmsapiProductImport;
use App\Modules\Rmsapi\src\Client as RmsapiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class ImportProductsJob implements ShouldQueue
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
     */
    public function handle()
    {
        $connections = RmsapiConnection::all();

        foreach ($connections as $connection) {

            $params = [
                'per_page' => '1',
                'order_by'=> 'db_change_stamp:asc',
            ];

            $products = RmsapiClient::GET($connection, 'api/products', $params);

            foreach ($products->getResult() as $product) {
                RmsapiProductImport::query()->create([
                   'connection_id' => $connection->id,
                   'raw_import' => $product
               ]);

                dd($product->asArray());
            }

        }

        ImportProductsJob::dispatch();
    }
}
