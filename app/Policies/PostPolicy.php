<?php
namespace App\Policies;

use App\Policies\Traits\OwnablePolicies;
use App\Models\Post;
use App\Models\User;
use App\Enums\PostTypeEnum;

class PostPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly', // ??
        'view'        => 'isOwner:pass isBlockedByOwner:fail',
        'update'      => 'isOwner:next:fail', // should auto fail any non-owners, but then move onto the update function for owners
        'delete'      => 'isOwner:next:fail',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass',
        'like'        => 'isOwner:next:fail isBlockedByOwner:fail',
        'comment'     => 'isOwner:next:fail isBlockedByOwner:fail',
    ];

    protected function index(User $user) 
    {
    }

    protected function view(User $user, Post $post)
    {
        //return $post->timeline->followers->contains($user->id);
        switch ($post->type) {
        case PostTypeEnum::FREE:
            return $post->timeline->followers->count()
                && $post->timeline->followers->contains($user->id);
        case PostTypeEnum::SUBSCRIBER:
            return $post->timeline->subscribers->count()
                && $post->timeline->subscribers->contains($user->id);
            //return $post->timeline->subscribers->contains($user->id);
            /*
            return $post->timeline->followers->count()
                && $post->timeline->followers()->wherePivot('access_level','premium')->count()
                && $post->timeline->followers()->wherePivot('access_level','premium')->contains($user->id);
             */
        case PostTypeEnum::PRICED:
            return $post->sharees->count()
                && $post->sharees->contains($user->id); // premium (?)
        }
    }

    /*
    protected function create(User $user)
    {
        throw new \Exception('check update policy for timeline instead');
    }
    */

    protected function update(User $user, Post $post)
    {
        switch ($post->type) {
        case PostTypeEnum::FREE:
            return true;
        case PostTypeEnum::SUBSCRIBER:
            return true; // %TODO
        case PostTypeEnum::PRICED:
            return !($post->ledgersales->count() > 0);
        }
    }

    protected function delete(User $user, Post $post)
    {
        switch ($post->type) {
        case PostTypeEnum::FREE:
            return true;
        case PostTypeEnum::SUBSCRIBER:
            return true; // %TODO
        case PostTypeEnum::PRICED:
            //return !($post->fanledgers->count() > 0);
            return $post->canBeDeleted();
        }
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

    protected function like(User $user, Post $post)
    {
        return $user->can('view', $post);
        //return $post->timeline->followers->contains($user->id);
    }

    protected function comment(User $user, Post $post)
    {
        return true; // DEBUG code...still get 403...putting dd here doesn't trigger
        return $user->can('view', $post);
    }

}
