<?php

namespace App\Policies;

// use App\Message;
use App\User;
use App\Policies\Traits\OwnablePolicies;

/**
 * **Stub** | Placeholder until message model is implemented.
 */

class MessagePolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isBlockedByOwner:fail', // If part of thread
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass',
    ];
}
