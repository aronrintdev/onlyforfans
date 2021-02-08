<?php

namespace App\Policies;

use App\User;
use App\VaultFolder;
use App\Policies\Traits\OwnablePolicies;

class VaultFolderPolicy extends BasePolicy
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

    protected function create(User $user)
    {
        return true;
    }
}
