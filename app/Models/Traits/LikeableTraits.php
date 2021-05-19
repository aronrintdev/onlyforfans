<?php

namespace App\Models\Traits;

use App\Models\User;

trait LikeableTraits
{
    /**
     * Likes on resource
     */
    public function likes()
    {
        //return $this->morphToMany(User::class, 'likeable', 'likeables', 'likee_id')->withTimestamps();
        return $this->morphToMany(User::class, 'likeable', 'likeables', 'likeable_id', 'likee_id')->withTimestamps();
    }

    /**
     * Number of likes
     */
    public function totalLikes()
    {
        return $this->likes()->count();
    }
}
