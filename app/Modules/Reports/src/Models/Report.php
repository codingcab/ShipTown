<?php

namespace App\Modules\Reports\src\Models;

use App\Exceptions\InvalidSelectException;
use App\Helpers\CsvBuilder;
use App\Modules\Reports\src\Http\Resources\ReportResource;
use App\Traits\HasTagsTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;
use Spatie\QueryBuilder\QueryBuilder;

class Report extends Model
{
    use HasTagsTrait;

    public $table = 'report';
    public string $report_name = 'Report';
    public string $view = 'report-default';

    public string $defaultSelect = '';
    public ?string $defaultSort = null;

    public array $toSelect = [];

    public array $fields = [];

    public array $initial_data = [];

    public mixed $baseQuery;

    private array $allowedFilters = [];
    public array $allowedIncludes = [];
    private array $fieldAliases = [];

    public function response($request): mixed
    {
        return $this->toView($request);
    }

    public function toView($request = null): mixed
    {
        $request = $request ?? request();

        if ($request->has('filename')) {
            return $this->csvDownload();
        }

        return $this->view();
    }

    public function toArray()
    {
        return $this->queryBuilder()
            ->simplePaginate(request()->get('per_page', $this->perPage))
            ->appends(request()->query());
    }

    public function queryBuilder(): QueryBuilder
    {
        $this->fieldAliases = [];

        foreach ($this->fields as $alias => $field) {
            $this->fieldAliases[] = $alias;
        }

        $queryBuilder = QueryBuilder::for($this->baseQuery);

        $queryBuilder = $this->addSelectFields($queryBuilder);

        if ($this->defaultSort) {
            $queryBuilder = $queryBuilder->defaultSort($this->defaultSort);
        }

        return $queryBuilder
            ->allowedFilters($this->getAllowedFilters())
            ->allowedSorts($this->fieldAliases)
            ->allowedIncludes($this->allowedIncludes);
    }

    private function view(): mixed
    {
        try {
            $queryBuilder = $this->queryBuilder()
                ->limit(request('per_page', $this->perPage));
        } catch (InvalidFilterQuery | InvalidSelectException $ex) {
            return response($ex->getMessage(), $ex->getStatusCode());
        }

        $resource = ReportResource::collection($queryBuilder->get());

        $data = [
            'report_name' => $this->report_name ?? $this->table,
            'fields' => $resource->count() > 0 ? array_keys((array)json_decode($resource[0]->toJson())) : [],
            'data' => $resource,
        ];

        $data['field_links'] = collect($data['fields'])->map(function ($field) {

            $sortIsDesc = request()->has('sort') && str_starts_with(request()->sort, '-');
            $currentSortName = str_replace('-', '', request()->sort);
            $isCurrent = $currentSortName === $field;
            $url = request()->fullUrlWithQuery(['sort' => $isCurrent && !$sortIsDesc ? "-".$field : $field]);

            return [
                'name' => $field,
                'url' => $url,
                'is_current' => $isCurrent,
                'is_desc' => $sortIsDesc,
                'display_name' => str_replace('_', ' ', ucwords($field, '_'))
            ];
        });

        return view($this->view, $data);
    }

    public function csvDownload()
    {
        $csv = CsvBuilder::fromQueryBuilder(
            $this->queryBuilder(),
            $this->fieldAliases
        );

        return response((string)$csv, 200, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-store, no-cache',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment; filename="' . request('filename', 'report.csv') . '"',
        ]);
    }

    /**
     * @param AllowedFilter $filter
     * @return $this
     */
    public function addFilter(AllowedFilter $filter): Report
    {
        $this->allowedFilters[] = $filter;

        return $this;
    }

    /**
     * @param $include
     * @return $this
     */
    public function addAllowedInclude($include): Report
    {
        $this->allowedIncludes[] = $include;

        return $this;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getAllowedFilters(): array
    {
        $filters = collect($this->allowedFilters);

        $filters = $filters->merge($this->addExactFilters());
        $filters = $filters->merge($this->addContainsFilters());
        $filters = $filters->merge($this->addInFilters());
        $filters = $filters->merge($this->addNotInFilters());
        $filters = $filters->merge($this->addBetweenStringFilters());
        $filters = $filters->merge($this->addBetweenFloatFilters());
        $filters = $filters->merge($this->addBetweenDatesFilters());
        $filters = $filters->merge($this->addGreaterThan());
        $filters = $filters->merge($this->addLowerThan());
        $filters = $filters->merge($this->addNullFilters());

        return $filters->toArray();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidSelectException
     */
    private function addSelectFields(QueryBuilder $queryBuilder): QueryBuilder
    {
        $requestedSelect = collect(explode(',', request()->get('select', $this->defaultSelect)))->filter();

        if ($requestedSelect->isEmpty()) {
            $requestedSelect = collect(array_keys($this->fields));
        }

        $requestedSelect
            ->each(function ($selectFieldName) use ($queryBuilder) {
                $fieldValue = data_get($this->fields, $selectFieldName);

                if ($fieldValue === null) {
                    throw new InvalidSelectException('Requested select field(s) `' . $selectFieldName . '` are not allowed.
                    Allowed select(s) are ' . collect(array_keys($this->fields))->implode(','));
                }

                if ($fieldValue instanceof Expression) {
                    $queryBuilder->addSelect(DB::raw('(' . $fieldValue . ') as ' . $selectFieldName));
                    return;
                }

                $queryBuilder->addSelect($fieldValue . ' as ' . $selectFieldName);
            });

        return $queryBuilder;
    }

    /**
     * @return array
     */
    private function addExactFilters(): array
    {

        $allowedFilters = [];

        // add exact filters
        collect($this->fields)
            ->each(function ($full_field_name, $alias) use (&$allowedFilters) {
                $allowedFilters[] = AllowedFilter::callback($alias, function ($query, $value) use ($full_field_name) {
                    return $query->where($full_field_name, '=', $value);
                });
            });

        return $allowedFilters;
    }


    /**
     * @return array
     */
    private function addContainsFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($value, $key) {
                $type = data_get($this->casts, $key);

                return in_array($type, ['string', null]);
            })
            ->each(function ($record, $alias) use (&$allowedFilters) {
                $filterName = $alias . '_contains';

                $allowedFilters[] = AllowedFilter::partial($filterName, $record);
            });

        return $allowedFilters;
    }

    /**
     * @return array
     */
    private function addBetweenFloatFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($fieldQuery, $fieldName) {
                $type = data_get($this->casts, $fieldName, 'string');
                return $type === 'float';
            })
            ->each(function ($fieldType, $fieldAlias) use (&$allowedFilters) {
                $filterName = $fieldAlias . '_between';
                $fieldQuery = $this->fields[$fieldAlias];

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($fieldType, $fieldAlias, $fieldQuery) {
                    // we add this to make sure query returns no records if array of two values is not specified
                    if ((!is_array($value)) or (count($value) != 2)) {
                        $query->whereRaw('1=2');
                        return;
                    }

                    if ($fieldQuery instanceof Expression) {
                        $query->whereBetween(DB::raw('(' . $fieldQuery . ')'), [floatval($value[0]), floatval($value[1])]);

                        return;
                    }

                    $query->whereBetween($fieldQuery, [floatval($value[0]), floatval($value[1])]);
                });
            });

        return $allowedFilters;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function addBetweenDatesFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($fieldQuery, $fieldName) {
                $type = data_get($this->casts, $fieldName, 'string');
                return in_array($type, ['datetime', 'date']);
            })
            ->each(function ($fieldType, $fieldAlias) use (&$allowedFilters) {
                $filterName = $fieldAlias . '_between';
                $fieldQuery = data_get($this->fields, $fieldAlias);

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($fieldType, $fieldAlias, $filterName, $fieldQuery) {
                    // we add this to make sure query returns no records if array of two values is not specified
                    if ((!is_array($value)) or (count($value) != 2)) {
                        throw new Exception($filterName . ': Invalid filter value, expected array of two values');
                    }

                    if ($fieldQuery instanceof Expression) {
                        $query->whereBetween(
                            DB::raw('(' . $fieldQuery . ')'),
                            [Carbon::parse($value[0]), Carbon::parse($value[1])]
                        );

                        return;
                    }

                    $query->whereBetween($fieldQuery, [Carbon::parse($value[0]), Carbon::parse($value[1])]);
                });
            });

        return $allowedFilters;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function addGreaterThan(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($fieldQuery, $fieldName) {
                $type = data_get($this->casts, $fieldName, 'string');
                return in_array($type, ['string', 'datetime', 'float']);
            })
            ->each(function ($record, $alias) use (&$allowedFilters) {
                $filterName = $alias . '_greater_than';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($alias, $filterName) {
                    $query->where($this->fields[$alias], '>', $value);
                });
            });

        return $allowedFilters;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function addGreaterThanFloat(): array
    {
        $allowedFilters = [];

        collect($this->casts)
            ->filter(function ($type) {
                return $type === 'float';
            })
            ->each(function ($record, $alias) use (&$allowedFilters) {
                $filterName = $alias . '_greater_than';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($alias, $filterName) {
                    $query->where($this->fields[$alias], '>', floatval($value));
                });
            });

        return $allowedFilters;
    }

    /**
     * @return array
     */
    private function addLowerThan(): array
    {
        $allowedFilters = [];

        collect($this->casts)
            ->filter(function ($type) {
                return in_array($type, ['string', 'datetime', 'float']);
            })
            ->each(function ($type, $alias) use (&$allowedFilters) {
                $filterName = $alias . '_lower_than';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($type, $alias) {
                    if ($type === 'float') {
                        $query->where($this->fields[$alias], '<', floatval($value));
                        return;
                    }

                    $query->where($this->fields[$alias], '<', $value);
                });
            });

        return $allowedFilters;
    }

    private function addNullFilters(): array
    {
        $allowedFilters = [];

//        collect($this->fields)
//            ->each(function ($record, $alias) use (&$allowedFilters) {
//                $filterName = 'null';

//                InvalidFilterValue::make($filterName);
                $allowedFilters[] = AllowedFilter::callback('null', function ($query, $value) {
                    $query->whereNull($this->fields[$value]);
                });
//            });

        return $allowedFilters;
    }

    public function simplePaginatedCollection(): Paginator
    {
        return $this->queryBuilder()->simplePaginate(request()->get('per_page', 10));
    }

    private function addBetweenStringFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->filter(function ($fieldQuery, $fieldName) {
                $type = data_get($this->casts, $fieldName, 'string');
                return $type === 'string';
            })
            ->each(function ($fieldType, $fieldAlias) use (&$allowedFilters) {
                $filterName = $fieldAlias . '_between';
                $fieldQuery = $this->fields[$fieldAlias];

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($fieldType, $fieldAlias, $fieldQuery) {
                    // we add this to make sure query returns no records if array of two values is not specified
                    if ((!is_array($value)) or (count($value) != 2)) {
                        $query->whereRaw('1=2');
                        return;
                    }

                    if ($fieldQuery instanceof Expression) {
                        $query->whereBetween(DB::raw('(' . $fieldQuery . ')'), [floatval($value[0]), floatval($value[1])]);

                        return;
                    }

                    $query->whereBetween($fieldQuery, [$value[0], $value[1]]);
                });
            });

        return $allowedFilters;
    }

    private function addNotInFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->each(function ($type, $alias) use (&$allowedFilters) {
                $filterName = $alias . '_not_in';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($type, $alias) {
                    $query->whereNotIn($this->fields[$alias], explode(',', $value));
                });
            });

        return $allowedFilters;
    }

    private function addInFilters(): array
    {
        $allowedFilters = [];

        collect($this->fields)
            ->each(function ($type, $alias) use (&$allowedFilters) {
                $filterName = $alias . '_in';

                $allowedFilters[] = AllowedFilter::callback($filterName, function ($query, $value) use ($type, $alias) {
                    $query->whereIn($this->fields[$alias], explode(',', $value));
                });
            });

        return $allowedFilters;
    }
}
