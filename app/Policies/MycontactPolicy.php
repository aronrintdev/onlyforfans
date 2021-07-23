<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Traits\OwnablePolicies;

class MycontactPolicy extends BasePolicy
{
    use OwnablePolicies;
    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
    ];

    /**
     * Adding new Mycontact
     *
     * @param User $user
     * @return true
     */
    protected function store(User $user)
    {
        return true;
    }

}