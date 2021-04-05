<?php

namespace App\Broadcasting;

use App\Models\User;

class UserPurchasesChannel
{
    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\Models\User  $user
     * @return array|bool
     */
    public function join(User $user, $userId)
    {
        return $user->id === $userId;
    }
}