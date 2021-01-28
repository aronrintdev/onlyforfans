<?php

namespace App\Policies;

// use App\Message;
use App\User;
use App\Policies\Addons\IsBlockedByOwner;
use App\Policies\Addons\IsOwner;

/**
 * **Stub** | Placeholder until message model is implemented.
 */

class MessagePolicy extends BasePolicy
{
    use IsBlockedByOwner;
    use IsOwner;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isBlockedByOwner:fail', // If part of thread
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass',
    ];
}
