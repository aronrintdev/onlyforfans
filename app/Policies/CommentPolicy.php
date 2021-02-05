<?php

namespace App\Policies;

use App\Comment;
use App\User;
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
        'toggleLike'  => 'isBlockedByOwner:fail',
    ];
}
