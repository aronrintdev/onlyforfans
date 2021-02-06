<?php
namespace App\Policies;

use App\User;
use App\Vaultfolder;
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
        'forceDelete' => 'isOwner:pass',
    ];

    protected function view(User $user, Vaultfolder $resource)
    {
        return $resource->sharees->contains($user->id);
    }

    protected function create(User $user)
    {
        return true; // %TODO: restrict to creators (?)
    }
}
