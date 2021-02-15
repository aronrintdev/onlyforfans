<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Timeline;
use App\Policies\Traits\OwnablePolicies;

class TimelinePolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'create'      => 'permissionOnly',  // User's don't really create timelines, but it may be something a admin may be able to do in the future.
        'view'        => 'isOwner:pass isBlockedByOwner:fail',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass',
        'follow'      => 'isOwner:pass isBlockedByOwner:fail',
        'like'        => 'isOwner:pass isBlockedByOwner:fail',
    ];

    protected function view(User $user, Timeline $resource)
    {
        return $resource->followers->contains($user->id);;
    }

    public function follow(User $user, Timeline $resource)
    {
        return !$this->isBlockedByOwner($user, $resource);
    }

    protected function like(User $user, Timeline $resource)
    {
        return $resource->followers->contains($user->id);;
    }

}
