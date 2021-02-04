<?php
namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Interfaces\Likeable;

class LikeablePolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    // post, story, comment, mediafile, etc
    public function like(User $user, Likeable $like)
    {
        if ( $like instanceof \App\Story ) {
            return $like->timeline->followers->contains($user->id);
        }
        return true; // for now allow all others - %FIXME
    }
}
