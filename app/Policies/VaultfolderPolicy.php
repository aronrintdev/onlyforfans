<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Vaultfolder;
use App\Policies\Traits\OwnablePolicies;

class VaultfolderPolicy extends BasePolicy
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
