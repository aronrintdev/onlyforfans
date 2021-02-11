<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Story;
use App\Interfaces\Likeable;
use Illuminate\Auth\Access\HandlesAuthorization;

// %FIXME: eventually replace this with specific model policies?
class LikeablePolicy extends BasePolicy
{
    // post, story, comment, mediafile, etc
    protected function like(User $user, Likeable $like)
    {
        if ( $like instanceof Story ) {
            return $like->timeline->followers->contains($user->id);
        }
        return false; // for now dis-allow all others - %FIXME
    }
}
