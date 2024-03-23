<?php

namespace App\Modules\Reports\src\Models;

use Spatie\QueryBuilder\AllowedFilter;

class SampleReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Sample Report';

        $this->fields = [
            'string_field'      => 'string_field',
            'float_field'       => 'float_field',
            'integer_field'     => 'integer_field',
            'date_field'        => 'date_field',
            'datetime_field'    => 'datetime_field',
        ];

        $this->casts = [
            'string_field'      => 'string',
            'float_field'       => 'float',
            'integer'           => 'integer',
            'date_field'        => 'date',
            'datetime_field'    => 'datetime',
        ];

        $this->addFilter(
            AllowedFilter::callback('custom_filter', function ($query, $value) {
                $query->where($value, true);
            })
        );
    }
}
