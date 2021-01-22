<?php

namespace App\Broadcasting;

use App\User;
/**
 * Monitors the online status of a specific user.
 * Can also be used to push user notifications to other connected users watching this channel.
 */
class UserStatusChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\User  $user
     * @return array|bool
     */
    public function join(User $user, $userId)
    {
        if ($user->id == $userId) {
            // Todo: Add check for hidden online here. If hidden they join as anon like everyone else.
            return [
                'id' => $user->id,
                'name' => $user->timeline->name,
            ];
        }
        // Todo: Block user that are blocked by this user
        // Id of guest prevents other users from know who each other are other than the user this channel is for.
        return [
            'id' => 'guest',
            'name' => 'guest',
        ];
    }

    /**
     * When user joins channel
     */
    public function subscribe($data, User $user, $userId)
    {
        if ( $user->id == $userId ) {
            $user->is_online = true;
            $user->save();
        }
    }

    /**
     * When user leaves channel
     */
    public function unsubscribe($data, User $user, $userId)
    {
        if ( $user->id == $userId ) {
            $user->is_online = false;
            $user->last_logged = \Carbon\Carbon::now();
            $user->save();
        }
    }

    /**
     * When a user raises an event.
     */
    public function clientEvent($event, $data, User $user, $userId) {
        //
    }

}
