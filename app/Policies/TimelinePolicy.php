<?php

namespace App\Policies;

use App\Timeline;
use App\User;
use App\Policies\Addons\IsBlockedByOwner;
use App\Policies\Addons\IsOwner;

class TimelinePolicy extends BasePolicy
{
    use IsBlockedByOwner;
    use IsOwner;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'create'      => 'permissionOnly',  // User's don't really create timelines, but it may be something a admin may be able to do in the future.
        'view'        => 'isBlockedByOwner:fail',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass',
    ];

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\Timeline  $timeline
     * @return mixed
     */
    public function view(User $user, Timeline $timeline)
    {
        // Is viewable by user?

        return true;
    }

}
