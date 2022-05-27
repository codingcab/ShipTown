<?php

namespace App\Modules\Automations\src\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer automation_id
 * @property string condition_class
 * @property string condition_value
 * @property Automation automation
 *
 */
class Condition extends BaseModel
{
    protected $table = 'modules_automations_conditions';

    protected $fillable = [
        'automation_id',
        'condition_class',
        'condition_value',
    ];

    public function isTrue($event): bool
    {
        $validator = new $this->condition_class($event);

        return $validator->isValid($this->condition_value);
    }


    /**
     * @return BelongsTo
     */
    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }
}
