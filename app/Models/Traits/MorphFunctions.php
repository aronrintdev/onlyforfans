<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;

trait MorphFunctions
{
    /**
     * Gets morph string for class or for current object class
     */
    public function getMorphString(string $class = null): string
    {
        if (!$class) {
            $class = get_class($this);
        }
        return array_flip(Relation::morphMap())[$class] ?? $class;
    }
}