<?php
namespace App\Policies;

use App\User;
use App\Vault;
use Illuminate\Auth\Access\HandlesAuthorization;

class VaultPolicy
{
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
}
