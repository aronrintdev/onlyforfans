<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends BasePolicy
{
    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'create'      => 'permissionOnly',
        'view'        => 'isBlockedBy:fail',
        'update'      => 'isSelf:pass',
        'delete'      => 'permissionOnly',
        'restore'     => 'permissionOnly',
        'forceDelete' => 'permissionOnly',
        'applyBan'    => 'permissionOnly',
        'removeBan'   => 'permissionOnly',
    ];

    public function isBlockedBy(User $sessionUser, User $user) : bool
    {
        return $sessionUser->$user->isBlockedBy($user);
    }

    public function isSelf(User $sessionUser, User $user) : bool
    {
        return $sessionUser->getKey() === $sessionUser->getKey();
    }
}
