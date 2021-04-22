<?php

namespace App\Traits;

use App\Interfaces\Ownable;
use App\Models\User;

/**
 * Use on models that implement Ownable
 */
trait OwnableFunctions
{
    /**
     * Check if a user is an owner
     */
    public function isOwner(User $user): bool
    {
        return $this instanceof Ownable
            ? $this->getOwner()->contains(function($value, $key) use($user) {
                return $value->getKey() === $user->getKey();
            })
            : false;
    }
}
