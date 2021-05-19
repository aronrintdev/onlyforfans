<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subscription;
use App\Policies\Traits\OwnablePolicies;


class SubscriptionPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'view'        => 'isOwner:pass',
        'update'      => 'isOwner:pass',
        'cancel'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'permissionOnly',
    ];

    /**
     * If user owns the subscribable item, allow to view
     *
     * @param User $user
     * @param Subscription $subscription
     * @return bool
     */
    protected function view(User $user, Subscription $subscription)
    {
        return $subscription->subscribable->isOwner($user);
    }

}