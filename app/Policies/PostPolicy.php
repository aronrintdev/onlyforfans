<?php
namespace App\Policies;

use App\User;
use App\Post;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function like(User $user, Post $resource)
    {
        return $resource->timeline->followers->contains($user->id)
            || $user->isOwner($resource);
    }

}
