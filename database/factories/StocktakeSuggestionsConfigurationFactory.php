<?php

namespace Database\Factories;

use App\Modules\StocktakeSuggestions\src\Models\StocktakeSuggestionsConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StocktakeSuggestionsConfigurationFactory extends Factory
{
    protected $model = StocktakeSuggestionsConfiguration::class;

    public function definition(): array
    {
        return [
            'min_count_date' => Carbon::now()->subMonth(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
