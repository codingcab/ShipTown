<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaggableResource;
use App\Models\Tag;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TagController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $query =  QueryBuilder::for(Tag::class)
            ->allowedFilters([
                'name',
                'type',
                AllowedFilter::scope('model')
            ]);

        return TaggableResource::collection($this->getPaginatedResult($query));
    }
}
