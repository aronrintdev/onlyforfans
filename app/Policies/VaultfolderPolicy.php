<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VaultFolder;
use App\Policies\Traits\OwnablePolicies;

class VaultFolderPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isBlockedByOwner:fail',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'permissionOnly',
    ];
}
