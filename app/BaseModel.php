<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{

    /**
     * @param string $attribute
     * @return bool
     */
    public function isAttributeChanged(string $attribute): bool
    {
        return $this->getOriginal($attribute) !== $this->getAttribute($attribute);
    }

    /**
     * @param array $attributes
     * @return bool
     */
    public function isAnyAttributeChanged(array $attributes): bool
    {
        $changedAttributes = collect($attributes)->filter(function (string $attribute) {
            return $this->isAttributeChanged($attribute);
        });

        return $changedAttributes->isNotEmpty();
    }
}
