<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{

    /** Aliases */
    public function getCattrsAttribute($value)
    {
        if ( isset($this->custom_attributes) ) {
            return $this->custom_attributes;
        }
        return $value;
    }

    public function setCattrsAttribute($value)
    {
        $this->attributes['custom_attributes'] = $value;
        $this->attributes['cattrs'] = $value;
    }

    public function getMetaAttribute($value)
    {
        if (isset($this->metadata)) {
            return $this->metadata;
        }
        return $value;
    }

    public function setMetaAttribute($value)
    {
        $this->attributes['metadata'] = $value;
        $this->attributes['meta'] = $value;
    }


    public function getMorphString(string $class) : string
    {
        return array_flip(Relation::morphMap())[$class] ?? $class;
    }
}