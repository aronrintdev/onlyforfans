<?php

namespace App\Policies\Addons;

use App\User;
use App\Policies\Enums\PolicyValidation;

trait IsBlockedByOwner
{
    use GetModelOwner;

    /**
     * Validate if user is the owner of the model
     * @param  User  $user
     * @param  object  $model
     */
    protected function isBlockedByOwner(User $user, $model)
    {
        $owner = $this->getModelOwner($model);
        if (isset($owner)) {
            if ( $user->isBlockedBy($owner) ) {
                return true;
            }
        }
        return false;
    }
}