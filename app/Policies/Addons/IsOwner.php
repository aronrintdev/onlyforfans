<?php

namespace App\Policies\Addons;

use App\User;
use App\Policies\Enums\PolicyValidation;

trait IsOwner
{
    use GetModelOwner;

    /**
     * Validate if user is the owner of the model
     * @param  User  $user
     * @param  object  $model
     */
    protected function isOwner(User $user, $model)
    {
        $owner = $this->getModelOwner($model);
        if (isset($owner)) {
            if ( class_basename($owner) === class_basename($user) ) {
                if ( $owner->getKey() === $user->getKey() ) {
                    return PolicyValidation::SUCCEED;
                }
            }
        }
        return PolicyValidation::CONTINUE;
    }
}