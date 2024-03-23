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
            'string_field_alias'      => 'string_field',
            'float_field_alias'       => 'float_field',
            'integer_field_alias'     => 'integer_field',
            'date_field_alias'        => 'date_field',
            'datetime_field_alias'    => 'datetime_field',
            'boolean_field_alias'     => 'boolean_field',
        ];

        $this->casts = [
            'string_field_alias'      => 'string',
            'float_field_alias'       => 'float',
            'integer_alias'           => 'integer',
            'date_field_alias'        => 'date',
            'datetime_field_alias'    => 'datetime',
            'boolean_field_alias'     => 'boolean',
        ];

        $this->addFilter(
            AllowedFilter::callback('custom_filter', function ($query, $value) {
                $query->where($value, true);
            })
        );
    }
}
