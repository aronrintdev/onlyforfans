<?php

namespace App\Models\Traits;

trait LikeableTraits
{
    /**
     * Likes on resource
     */
    public function likes()
    {
        //return $this->morphToMany('App\Models\User', 'likeable', 'likeables', 'likee_id')->withTimestamps();
        return $this->morphToMany('App\Models\User', 'likeable', 'likeables', 'likeable_id', 'likee_id')->withTimestamps();
    }

    /**
     * Number of likes
     */
    public function totalLikes()
    {
        return $this->likes()->count();
    }
}
