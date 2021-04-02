<?php

namespace App\Broadcasting;

use App\Models\User;

class UserEvents
{
    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\Models\User  $user
     * @return array|bool
     */
    public function join(User $user, $userId)
    {
        if ($user->id == $userId) {
            return [
                'id' => $user->id,
                'name' => $user->timeline->name,
            ];
        }
    }
}
