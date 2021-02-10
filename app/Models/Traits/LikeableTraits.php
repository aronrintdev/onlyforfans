<?php

namespace App\Models\Traits;

trait LikeableTraits
{
    /**
     * Likes on resource
     */
    public function likes()
    {
        return $this->morphToMany('App\Models\User', 'likeable', 'likeables')
            ->withTimestamps();
    }

    /**
     * Number of likes
     */
    public function totalLikes()
    {
        return $this->likes()->count();
    }
}