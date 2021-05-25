<?php

namespace App\Policies\Financial;

use App\Models\User;
use App\Policies\BasePolicy;
use App\Models\Financial\AchAccount;
use App\Policies\Traits\OwnablePolicies;

class AchAccountPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass',
        'update'      => 'isOwner:pass:fail',
        'delete'      => 'isOwner:pass:fail',
        'restore'     => 'isOwner:pass:fail',
        'forceDelete' => 'permissionOnly',
    ];

    protected function create(User $user)
    {
        // TODO: Check if creator account or not?
        // TODO: Check if creator is ID verified?
        return true;
    }
}
