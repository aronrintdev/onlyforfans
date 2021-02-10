<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Vault;
use App\Policies\Traits\OwnablePolicies;

class VaultPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass',
    ];
}
