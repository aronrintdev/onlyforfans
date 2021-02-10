<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    public function getMorphString(string $class) : string
    {
        return array_flip(Relation::morphMap())[$class] ?? $class;
    }
}