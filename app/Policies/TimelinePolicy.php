<?php
namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Timeline;
use App\User;

class TimelinePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        //
    }

    public function view(User $user, Timeline $resource)
    {
        return $user->isOwner($resource)
            || $resource->followers->contains($user->id);
    }

    public function create(User $user)
    {
        //
    }

    public function update(User $user, Timeline $resource)
    {
        //
    }

    public function delete(User $user, Timeline $resource)
    {
        //
    }

    public function restore(User $user, Timeline $resource)
    {
        //
    }

    public function forceDelete(User $user, Timeline $resource)
    {
        //
    }
}
