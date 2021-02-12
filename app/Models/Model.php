<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{

    //protected $customAttributesField = 'cattrs';
    //protected $metaField = 'meta';

    /** Aliases */
    /*
    public function getCattrsAttribute($value)
    {
        return $this->{$this->customAttributesField};
    }

    public function setCattrsAttribute($value)
    {
        $this->attributes[$this->customAttributesField] = $value;
    }

    public function getMetaAttribute($value)
    {
        return $this->{$this->metaField};
    }

    public function setMetaAttribute($value)
    {
        $this->attributes[$this->metaField] = $value;
    }
     */


    public function getMorphString(string $class) : string
    {
        return array_flip(Relation::morphMap())[$class] ?? $class;
    }
}
