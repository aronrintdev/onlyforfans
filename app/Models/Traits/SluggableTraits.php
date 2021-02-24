<?php

namespace App\Models\Traits;

use Cviebrock\EloquentSluggable\Sluggable;

trait SluggableTraits
{
    /**
     * Attempts to find model by slug, then by id
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = isset($field) ? $field : 'slug';
        $model = $this->where($field, $value)->first();
        if (isset($model) === false) {
            $model = $this->find($value);
        }
        return $model;
    }
}
