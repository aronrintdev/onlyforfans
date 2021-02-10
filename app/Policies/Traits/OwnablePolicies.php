<?php

namespace App\Policies\Traits;

use App\Models\User;
use App\Interfaces\Ownable;

/**
 * Policy functions related to App\Interfaces\Ownable
 */
trait OwnablePolicies
{
    /**
     * Validate if user is the owner of the model
     * @param  User  $user
     * @param  object  $model
     */
    protected function isOwner(User $user, $model)
    {
        return $model instanceof Ownable ? $model->isOwner($user) : false;
    }

    /**
     * Validate if user is the owner of the model
     * @param  User  $user
     * @param  object  $model
     */
    protected function isBlockedByOwner(User $user, $model)
    {
        if (!$model instanceof Ownable) {
            return false;
        }
        return $model->getOwner()->contains(function ($value, $key) use ($user) {
            return $user->isBlockedBy($value);
        });
    }
}
