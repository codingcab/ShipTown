<?php

namespace App\Models;

use App\BaseModel;

/**
 * @property int $id
 * @property int $tag_id
 * @property int $taggable_id
 * @property string $taggable_type
 *
 */
class Taggable extends BaseModel
{
    protected $table = 'taggables';

    protected $fillable = [
        'tag_id',
        'taggable_type',
        'taggable_id',
    ];
}
