<?php

namespace App\Modules\StocktakeSuggestions\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StocktakeSuggestionsConfiguration extends Model
{
    use HasFactory;

    protected $table = 'modules_stocktaking_suggestions_configurations';

    protected $fillable = [
        'min_count_date',
    ];

    protected $casts = [
        'min_count_date' => 'date',
    ];
}
