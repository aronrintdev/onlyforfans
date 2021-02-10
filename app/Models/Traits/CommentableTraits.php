<?php

namespace App\Models\Traits;

trait CommentableTraits
{
    public function comments()
    {
        return $this->morphMany('App\Models\Comment', 'commentable');
    }
}