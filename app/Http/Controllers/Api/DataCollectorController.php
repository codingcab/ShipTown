<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiDataCollectorStoreRequest;
use App\Http\Requests\ApiDataCollectorUpdateRequest;
use App\Http\Resources\DataCollectionResource;
use App\Models\DataCollection;
use App\Modules\DataCollector\src\Services\DataCollectorService;
use App\Modules\Reports\src\Models\DataCollectorListReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class DataCollectorController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $report = new DataCollectorListReport();

        return JsonResource::collection($report->respondArray());
    }

    public function store(ApiDataCollectorStoreRequest $request): DataCollectionResource
    {
        $dc = DataCollection::create($request->validated());

        return DataCollectionResource::make($dc);
    }

    public function update(ApiDataCollectorUpdateRequest $request, int $data_collection_id): DataCollectionResource
    {
        $data_collector = DataCollection::findOrFail($data_collection_id);
        $data_collector->update($request->validated());

        optional($request->get('action'), function ($action) use ($data_collector) {
            DataCollectorService::runAction($data_collector, $action);
        });

        return DataCollectionResource::make($data_collector->refresh());
    }

    public function destroy(int $data_collection_id): DataCollectionResource
    {
        $data_collector = DataCollection::findOrFail($data_collection_id);
        $data_collector->delete();

        return DataCollectionResource::make($data_collector);
    }
}
