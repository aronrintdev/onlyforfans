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

    public function view(User $user, Timeline $timeline)
    {
        return $user->isOwner($timeline)
            || $timeline->followers->contains($user->id);
    }

    public function create(User $user)
    {
        //
    }

    public function update(User $user, Timeline $timeline)
    {
        //
    }

    public function delete(User $user, Timeline $timeline)
    {
        //
    }

    public function restore(User $user, Timeline $timeline)
    {
        //
    }

    public function forceDelete(User $user, Timeline $timeline)
    {
        //
    }
}
