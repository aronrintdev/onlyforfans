<?php
namespace App\Policies;

use App\Policies\Traits\OwnablePolicies;
use App\Post;
use App\User;
use App\Enums\PostTypeEnum;

class PostPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass isBlockedByOwner:fail',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass',
        'like'        => 'isOwner:pass isBlockedByOwner:fail',
    ];

    protected function view(User $user, Post $post)
    {
        //return $post->timeline->followers->contains($user->id);
        switch ($post->type) {
        case PostTypeEnum::FREE:
            return $post->timeline->followers->contains($user->id);
        case PostTypeEnum::SUBSCRIBER:
            //return $post->timeline->subscribers->contains($user->id);
            return $post->timeline->followers()->wherePivot('access_level','premium')->contains($user->id);
        case PostTypeEnum::PRICED:
            return $post->sharees->contains($user->id); // premium (?)
        }
    }

    protected function create(User $user)
    {
        // Not blocked from creating posts?
        // TODO: Add method when logic is finalized.
        return true;
    }

    protected function restore(User $user, Post $post)
    {
        return false;
    }

    protected function forceDelete(User $user, Post $post)
    {
        return false;
    }

    protected function tip(User $user, Post $post)
    {
        return true;
    }

    public function like(User $user, Post $resource)
    {
        return $resource->timeline->followers->contains($user->id);
    }

}
