<?php

namespace App\Policies\Financial;

use App\Models\User;
use App\Policies\BasePolicy;
use App\Models\Financial\Account;
use App\Policies\Traits\OwnablePolicies;

class AccountPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass',
        'update'      => 'isOwner:next:fail',
        'delete'      => 'isOwner:next:fail',
        'restore'     => 'isOwner:pass:fail',
    ];

    protected function delete(User $user, Account $account)
    {
        return true;
    }
}