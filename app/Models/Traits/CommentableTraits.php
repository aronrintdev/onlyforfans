<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait CommentableTraits
{
    public function comments(): MorphMany
    {
        return $this->morphMany('App\Models\Comment', 'commentable');
    }
}