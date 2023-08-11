<?php

namespace App\Http\Controllers\Api\Modules\MagentoApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\MagentoApiConnectionUpdateRequest;
use App\Http\Resources\MagentoConnectionResource;
use App\Modules\MagentoApi\src\Http\Requests\MagentoApiConnectionDestroyRequest;
use App\Modules\MagentoApi\src\Http\Requests\MagentoApiConnectionIndexRequest;
use App\Modules\MagentoApi\src\Http\Requests\MagentoApiConnectionStoreRequest;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Tags\Tag;

class MagentoApiConnectionController extends Controller
{
    public function index(MagentoApiConnectionIndexRequest $request): AnonymousResourceCollection
    {
        $query = MagentoConnection::getSpatieQueryBuilder();

        return MagentoConnectionResource::collection($this->getPaginatedResult($query));
    }

    public function store(MagentoApiConnectionStoreRequest $request): MagentoConnectionResource
    {
        $connection = new MagentoConnection();
        $connection->fill($request->only($connection->getFillable()));

        if ($request->has('tag')) {
            $tag = Tag::findOrCreate($request->get('tag'));
            $connection->inventory_source_warehouse_tag_id = $tag->getKey();
        }

        $connection->save();

        return new MagentoConnectionResource($connection);
    }

    public function update(MagentoApiConnectionUpdateRequest $request, MagentoConnection $connection): MagentoConnectionResource
    {
        $connection->update($request->validated());

        if ($request->has('inventory_source_warehouse_tag_id')) {
            $tag = Tag::query()->find($request->get('inventory_source_warehouse_tag_id'));
            $connection->syncTags([$tag]);
        }

        return MagentoConnectionResource::make($connection);
    }

    public function destroy(MagentoApiConnectionDestroyRequest $request, MagentoConnection $connection): JsonResponse
    {
        $connection->delete();

        return response()->json([], 204);
    }
}
