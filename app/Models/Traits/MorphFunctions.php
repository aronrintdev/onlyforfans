<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;

trait MorphFunctions
{
    /**
     * Gets morph string for class or for current object class
     * @return string
     */
    public function getMorphString(string $class = null): string
    {
        if (!$class) {
            $class = get_class($this);
        }
        return array_flip(Relation::morphMap())[$class] ?? $class;
    }

    /**
     * Gets morph string for current model class statically
     * @return string
     */
    public static function getMorphStringStatic(): string
    {
        return array_flip(Relation::morphMap())[static::class] ?? static::class;
    }
}