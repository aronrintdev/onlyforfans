<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use App\Policies\Traits\OwnablePolicies;

class CommentPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isBlockedByOwner:fail',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass',
        'like'        => 'isOwner:pass',
    ];

    protected function like(User $user, Comment $resource)
    {
        return $resource->post->timeline->followers->contains($user->id);
    }

}
