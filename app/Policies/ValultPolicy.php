<?php
namespace App\Policies;

use App\User;
use App\Vault;
use App\Policies\Traits\OwnablePolicies;

class VaultPolicy extends BasePolicy
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
    /*
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
    }

    public function view(User $user, Vault $resource)
    {
        return $user->isOwner($resource);
    }

    public function create(User $user)
    {
        return $user->isOwner($resource);
    }

    public function update(User $user, Vault $resource)
    {
        return $user->isOwner($resource);
    }

    public function delete(User $user, Vault $resource)
    {
        return $user->isOwner($resource);
    }

    public function restore(User $user, Vault $resource)
    {
        return $user->isOwner($resource);
    }

    public function forceDelete(User $user, Vault $resource)
    {
        return $user->isOwner($resource);
    }
     */
}
