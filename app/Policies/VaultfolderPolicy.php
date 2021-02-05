<?php
namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\User;
use App\Vaultfolder;

class VaultfolderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
    }

    public function view(User $user, Vaultfolder $resource)
    {
        return $user->isOwner($resource)
            || $resource->sharees->contains($user->id);
    }

    public function create(User $user)
    {
        return $user->isOwner($resource);
    }

    public function update(User $user, Vaultfolder $resource)
    {
        return $user->isOwner($resource);
    }

    public function delete(User $user, Vaultfolder $resource)
    {
        return $user->isOwner($resource);
    }

    public function restore(User $user, Vaultfolder $resource)
    {
        return $user->isOwner($resource);
    }

    public function forceDelete(User $user, Vaultfolder $resource)
    {
        return $user->isOwner($resource);
    }
}
