<?php

namespace App\Modules\Reports\src\Models;

use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class SampleReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Sample Report';

        $this->baseQuery = DB::query();

        $this->fields = [
            'string_field'      => 'Blue Black Red',
            'float_field'       => '12.34',
            'datetime_field'    => '2005-05-20 12:13:14',
            'date_field'        => '2005-05-20',
        ];

        $this->casts = [
            'string_field'      => 'string',
            'float_field'       => 'float',
            'datetime_field'    => 'datetime',
            'date_field'        => 'date',
        ];

        $this->addFilter(
            AllowedFilter::callback('custom_filter', function ($query, $value) {
                $query->where($value, true);
            })
        );
    }
}
