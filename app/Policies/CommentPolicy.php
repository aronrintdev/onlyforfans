<?php

namespace App\Policies;

use App\Comment;
use App\User;
use App\Policies\Addons\IsBlockedByOwner;
use App\Policies\Addons\IsOwner;

class CommentPolicy extends BasePolicy
{
    use IsBlockedByOwner;
    use IsOwner;

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
