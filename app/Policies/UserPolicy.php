<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends BasePolicy
{
    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'create'      => 'permissionOnly',
        'view'        => 'isSelf:pass',
        'update'      => 'isSelf:pass',
        'delete'      => 'permissionOnly',
        'restore'     => 'permissionOnly',
        'forceDelete' => 'permissionOnly',
        'applyBan'    => 'permissionOnly',
        'removeBan'   => 'permissionOnly',
    ];

    protected function isSelf(User $sessionUser, User $user) : bool
    {
        return $sessionUser->getKey() === $user->getKey();
    }

    protected function isBlockedBy(User $sessionUser, User $user) : bool
    {
        //return $sessionUser->$user->isBlockedBy($user);
        return $sessionUser->isBlockedBy($user); // %TODO %CHECKME
    }

}
