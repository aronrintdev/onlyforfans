<?php

namespace App\Policies;

use App\Vault;
use App\User;
use App\Policies\Addons\IsBlockedByOwner;
use App\Policies\Addons\IsOwner;

class VaultPolicy extends BasePolicy
{
    use IsBlockedByOwner;
    use IsOwner;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isBlockedByOwner:fail',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'permissionOnly',
    ];
}
