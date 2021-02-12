<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{

    protected $customAttributesField = 'custom_attributes';
    protected $metadataField = 'metadata';

    /** Aliases */
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
        return $this->{$this->metadataField};
    }

    public function setMetaAttribute($value)
    {
        $this->attributes[$this->metadataField] = $value;
    }


    public function getMorphString(string $class) : string
    {
        return array_flip(Relation::morphMap())[$class] ?? $class;
    }
}