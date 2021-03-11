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
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass isBlockedByOwner:fail', // if owner pass, if blocked fail, else check function
        'contentView' => 'isOwner:pass isBlockedByOwner:fail',
        'like'        => 'isOwner:pass isBlockedByOwner:fail',
        'comment'     => 'isOwner:pass isBlockedByOwner:fail',
        'purchase'    => 'isOwner:fail isBlockedByOwner:fail',
        'tip'         => 'isOwner:fail isBlockedByOwner:fail',
        'update'      => 'isOwner:next:fail', // if non-owner fail, else check function
        'delete'      => 'isOwner:next:fail',
        'forceDelete' => 'isOwner:next:fail',
        'restore'     => 'isOwner:pass:fail',
    ];

    /*
    protected function index(User $user) 
    {
    }
     */

    protected function view(User $user, Post $post)
    {
        // %NOTE: followers can view all posts, but certain fields (description, mediafiles, etc) will be hidden/locked based on subscriber or purchase status
        //return $post->timeline->followers->contains($user->id);
        return true; // essentially allow viewing of free posts by 'public' (non-followers)
    }

    protected function contentView(User $user, Post $post)
    {
        // content view (eg, images attached to the post) is checked granularly: dep on post type and user's 'status'
        switch ($post->type) {
        case PostTypeEnum::FREE:
            return $post->timeline->followers->count()
                && $post->timeline->followers->contains($user->id);
        case PostTypeEnum::SUBSCRIBER:
            return $post->timeline->subscribers->count()
                && $post->timeline->subscribers->contains($user->id);
            //return $post->timeline->followers->count()
                //&& $post->timeline->followers()->wherePivot('access_level','premium')->count()
                //&& $post->timeline->followers()->wherePivot('access_level','premium')->contains($user->id);
        case PostTypeEnum::PRICED:
            return $post->sharees->count()
                && $post->sharees->contains($user->id); // premium (?)
        }
        //return $post->timeline->followers->contains($user->id);
        //return $post->timeline->subscribers->contains($user->id);
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

    protected function purchase(User $user, Post $post)
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
        return $user->can('view', $post);
    }

    protected function isBlockedBy(User $sessionUser, User $user) : bool
    {
        return $sessionUser->$user->isBlockedBy($user);
    }

}
