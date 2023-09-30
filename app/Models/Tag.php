<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends \Spatie\Tags\Tag
{
    use HasFactory;

    public function taggables(): HasMany
    {
        return $this->hasMany(Taggable::class);
    }

    public function scopeModel($query, $model)
    {
        return $query->whereHas('taggables', function ($query) use ($model) {
            $query->where('taggable_type', $model);
        });
    }
}
