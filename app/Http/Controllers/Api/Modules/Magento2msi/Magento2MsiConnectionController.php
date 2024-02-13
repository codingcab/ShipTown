<?php

namespace App\Http\Controllers\Api\Modules\Magento2msi;

use App\Http\Controllers\Controller;
use App\Modules\Magento2MSI\src\Api\MagentoApi;
use App\Modules\Magento2MSI\src\Jobs\CheckIfSyncIsRequiredJob;
use App\Modules\Magento2MSI\src\Jobs\FetchStockItemsJob;
use App\Modules\Magento2MSI\src\Models\Magento2msiConnection;
use App\Modules\Magento2MSI\src\Models\Magento2msiProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class Magento2MsiConnectionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $connections = Magento2msiConnection::getSpatieQueryBuilder()->get()->collect();

        $connections = $connections->map(function ($connection) {
            return array_merge($connection->toArray(), [
                'inventory_sources' => MagentoApi::getInventorySources($connection)->json('items'),
            ]);
        });

        return JsonResource::collection($connections);
    }

    public function store(Request $request)
    {
        return Magento2msiConnection::create($request->all());
    }

    public function update(Request $request, $connection_id): JsonResource
    {
        $connection = Magento2msiConnection::findOrFail($connection_id);

        $connection->update($request->all());

        Magento2msiProduct::query()->where('connection_id', $connection_id)->delete();

        FetchStockItemsJob::dispatchAfterResponse();

        return JsonResource::make($connection);
    }
}