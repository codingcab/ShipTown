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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;
use Spatie\QueryBuilder\QueryBuilder;

class Report extends Model
{
    use HasTagsTrait;

    public $table = 'temporary_report_table';

    public string $report_name = 'Report';
    public string $view = 'report-default';

    public ?string $defaultSelect = null;

    public ?string $defaultSort = null;

    public array $fields = [];

    public mixed $baseQuery = null;

    private array $allowedFilters = [];
    public array $allowedIncludes = [];
    private array $fieldAliases = [];

    public function response($request = null): mixed
    {
        $request = $request ?? request();

        if ($request->has('filename')) {
            $fileExtension = pathinfo($request->input('filename'), PATHINFO_EXTENSION);

            if ($fileExtension === 'csv') {
                return $this->csvDownload();
            }

            if ($fileExtension === 'json') {
                return $this->respondJson();
            }

            return response('Invalid file extension. Only CSV, JSON files are allowed.', 400);
        }

        return $this->toView();
    }

    /**
     * @throws InvalidSelectException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function queryBuilder(): QueryBuilder
    {
        $this->fieldAliases = array_keys($this->fields);

        $queryBuilder = QueryBuilder::for($this->baseQuery ?? $this);

        $queryBuilder = $this->addSelectFields($queryBuilder);

        if ($this->defaultSort) {
            $queryBuilder = $queryBuilder->defaultSort($this->defaultSort);
        }

        return $queryBuilder
            ->allowedFilters($this->getAllowedFilters())
            ->allowedSorts($this->fieldAliases)
            ->allowedIncludes($this->allowedIncludes);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws InvalidSelectException
     * @throws ContainerExceptionInterface
     */
    public function respondArray(): Paginator
    {
        return $this->queryBuilder()
            ->simplePaginate(request()->get('per_page', $this->perPage))
            ->appends(request()->query());
    }

    private function toView(): mixed
    {
        try {
            $queryBuilder = $this->queryBuilder()->limit(request('per_page', $this->perPage));
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

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidSelectException
     * @throws NotFoundExceptionInterface
     */
    public function csvDownload(): Response
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
     * @throws InvalidSelectException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function respondJson(): AnonymousResourceCollection
    {
        $csv = $this->queryBuilder()->get();

        return JsonResource::collection($csv);
    }

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

        $allowedFilters = [];

        collect($this->fields)
            ->each(function ($fieldExpression, $fieldAlias) use (&$allowedFilters) {
                if ($fieldExpression instanceof Expression) {
                    $finalFieldExpression = DB::raw('(' . $fieldExpression . ')');
                } else {
                    $finalFieldExpression = $this->fields[$fieldAlias];
                }

                // equal
                $allowedFilters[] = AllowedFilter::callback($fieldAlias, function ($query, $value) use ($finalFieldExpression, $fieldAlias) {
                    $query->where($finalFieldExpression, '=', $value);
                });

                // not equal
                $allowedFilters[] = AllowedFilter::callback($fieldAlias . '_not_equal', function ($query, $value) use ($finalFieldExpression, $fieldAlias) {
                    $query->where($finalFieldExpression, '!=', $value);
                });

                // in
                $allowedFilters[] = AllowedFilter::callback($fieldAlias . '_in', function ($query, $value) use ($finalFieldExpression, $fieldAlias) {
                    $query->whereIn($finalFieldExpression, $value);
                });

                // not in
                $allowedFilters[] = AllowedFilter::callback($fieldAlias . '_not_in', function ($query, $value) use ($finalFieldExpression, $fieldAlias) {
                    $query->whereNotIn($finalFieldExpression, $value);
                });

                // greater than
                $allowedFilters[] = AllowedFilter::callback($fieldAlias . '_greater_than', function ($query, $value) use ($finalFieldExpression, $fieldAlias) {
                    $query->where($finalFieldExpression, '>', $value);
                });

                // lower than
                $allowedFilters[] = AllowedFilter::callback($fieldAlias . '_lower_than', function ($query, $value) use ($finalFieldExpression, $fieldAlias) {
                    $query->where($finalFieldExpression, '<', $value);
                });

                // is null
                $allowedFilters[] = AllowedFilter::callback($fieldAlias . '_is_null', function ($query) use ($finalFieldExpression, $fieldAlias) {
                    $query->whereNull($finalFieldExpression);
                });

                // contains
                if ($this->isOfType($fieldAlias, ['string'])) {
                    $allowedFilters[] = AllowedFilter::callback($fieldAlias . '_contains', function ($query, $value) use ($finalFieldExpression, $fieldAlias) {
                        $query->where($finalFieldExpression, 'like', '%' .$value. '%');
                    });
                }

                $allowedFilters[] = $this->betweenFilter($fieldAlias, $fieldExpression);
            });

        return $filters->merge($allowedFilters)->toArray();
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
        $requestedSelects = request()->get('select');

        if ($requestedSelects) {
            $fieldsToSelect = explode(',', $requestedSelects);
        } elseif ($this->defaultSelect) {
            $fieldsToSelect = explode(',', $this->defaultSelect);
        } else {
            $fieldsToSelect = array_keys($this->fields);
        }

        collect($fieldsToSelect)->filter()
            ->each(function ($fieldAlias) use ($queryBuilder) {
                $fieldValue = data_get($this->fields, $fieldAlias);

                if ($fieldValue === null) {
                    throw new InvalidSelectException(implode(' ', [
                        'Requested select field `' . $fieldAlias . '` is not allowed.',
                        'Allowed select(s) are', implode(', ', array_keys($this->fields))
                    ]));
                }

                if ($fieldValue instanceof Expression) {
                    $queryBuilder->addSelect(DB::raw('(' . $fieldValue . ') as ' . $fieldAlias));
                    return true;
                }

                $queryBuilder->addSelect($fieldValue . ' as ' . $fieldAlias);
            });

        return $queryBuilder;
    }

    public function isOfType($fieldName, array $expectedTypes): bool
    {
        $fieldType = data_get($this->casts, $fieldName, 'string');

        return in_array($fieldType, $expectedTypes);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws InvalidSelectException
     * @throws ContainerExceptionInterface
     */
    public function simplePaginatedCollection(): Paginator
    {
        $per_page = request()->get('per_page', 10);

        return $this->queryBuilder()->simplePaginate($per_page);
    }

    private function betweenFilter(string $fieldAlias, string $fieldName): AllowedFilter
    {
        return AllowedFilter::callback($fieldAlias . '_between', function ($query, $value) use ($fieldAlias, $fieldName) {
            // we add this to make sure query returns no records if array of two values is not specified
            if ((!is_array($value)) or (count($value) != 2)) {
                $query->whereRaw('1=2');
                return;
            }

            if ($fieldName instanceof Expression) {
                $fieldQuery = DB::raw('(' . $fieldName . ')');
            } else {
                $fieldQuery = $this->fields[$fieldAlias];
            }

            $value1 = $value[0];
            $value2 = $value[1];

            if ($this->isOfType($fieldName, ['float'])) {
                $value1 = floatval($value1);
                $value2 = floatval($value2);
            }

            if ($this->isOfType($fieldName, ['datetime', 'date'])) {
                $value1 = Carbon::parse($value1);
                $value2 = Carbon::parse($value2);
            }

            $query->whereBetween($fieldQuery, [$value1, $value2]);
        });
    }
}
