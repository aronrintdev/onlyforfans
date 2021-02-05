<?php
namespace App\Policies;

use App\User;
use App\Comment;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function like(User $user, Comment $resource)
    {
        return $resource->post->timeline->followers->contains($user->id)
            || $user->isOwner($resource);
    }

}
