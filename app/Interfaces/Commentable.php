<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Commentable extends IsModel
{
    public function comments(): MorphMany;

    public function getPrimaryOwner();
}