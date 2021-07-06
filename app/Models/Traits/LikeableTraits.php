<?php

namespace App\Models\Traits;

use App\Models\User;

trait LikeableTraits
{
    /**
     * Likes on resource (not quite correct, see comment in %NOTE below)
     // %TODO %FIXME: probably should be renamed to likers() instead of likes() */
    public function likes()
    {
        // %NOTE: this refers to the *users* who have liked the post, not the likeable object itself!!!! (??)
        return $this->morphToMany(User::class, 'likeable', 'likeables', 'likeable_id', 'liker_id')->withTimestamps();
    }

    /**
     * Number of likes
     */
    public function totalLikes()
    {
        return $this->likes()->count();
    }
}
