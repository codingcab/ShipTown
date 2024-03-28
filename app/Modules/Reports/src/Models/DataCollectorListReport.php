<?php

namespace App\Modules\Reports\src\Models;

use App\Models\DataCollection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class DataCollectorListReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Data Collector List';

        $this->baseQuery = DataCollection::query()
            ->leftJoin('warehouses', 'warehouses.id', '=', 'data_collections.warehouse_id');

        $differences_count_subquery = DB::raw('(
            SELECT count(*)
            FROM data_collection_records as dcr
            WHERE dcr.data_collection_id = data_collections.id
            AND IFNULL(dcr.quantity_requested, 0) != (dcr.total_transferred_in + dcr.total_transferred_out)
        )');

        $this->fields = [
            'id'                    => 'data_collections.id',
            'warehouse_code'        => 'warehouses.code',
            'warehouse_name'        => 'warehouses.name',
            'warehouse_id'          => 'warehouse_id',
            'type'                  => 'data_collections.type',
            'name'                  => 'data_collections.name',
            'differences_count'     =>  $differences_count_subquery,
            'created_at'            => 'data_collections.created_at',
            'updated_at'            => 'data_collections.updated_at',
            'deleted_at'            => 'data_collections.deleted_at',
            'currently_running_task'=> 'data_collections.currently_running_task',
        ];

        $this->casts = [
            'id'                     => 'integer',
            'warehouse_id'           => 'integer',
            'type'                   => 'string',
            'name'                   => 'string',
            'created_at'             => 'datetime',
            'updated_at'             => 'datetime',
            'deleted_at'             => 'datetime',
            'warehouse_code'         => 'string',
            'warehouse_name'         => 'string',
            'currently_running_task' => 'string',
        ];

        $this->addFilter(
            AllowedFilter::callback('with_archived', function ($query, $value) {
                if ($value === true) {
                    $query->withTrashed();
                }
            })
        );
        $this->addFilter(
            AllowedFilter::callback('only_archived', function ($query, $value) {
                if ($value === true) {
                    $query->onlyTrashed();
                }
            })
        );

        $this->addAllowedInclude('comments');
        $this->addAllowedInclude('comments.user');
    }
}
