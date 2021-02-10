<?php
namespace App\Policies;

use App\User;
use App\Story;
use App\Policies\Traits\OwnablePolicies;

class StoryPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass isBlockedByOwner:fail',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'permissionOnly',
    ];

    protected function view(User $user, Story $story)
    {
        return $story->timeline->followers->contains($user->id);
    }

    protected function create(User $user)
    {
        return true;
    }
}
