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
        $model = $this->where('slug', $value)->first();
        if (isset($model) === false) {
            $model = $this->find($value);
        }
        return $model;
    }
}
