<?php

namespace App\Broadcasting;

use App\Models\User;
use App\Models\Chatthread;
/**
 * Monitors the online status of a specific user.
 * Can also be used to push user notifications to other connected users watching this channel.
 */
class ChatthreadChannel
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
     * @param  \App\Models\User  $user
     * @param  string $chatthread
     * @return array|bool
     */
    public function join(User $user, Chatthread $chatthread)
    {
        // Only allow users that are a part of this thread
        if (
            $chatthread->participants->contains(function ($value) use($user) {
                return $value->id === $user->id;
            })
        ) {
            return [
                'id' => $user->id,
                'name' => $user->timeline->name,
            ];
        }
    }

    /**
     * When a user raises an event.
     */
    public function clientEvent($event, $data, User $user, $userId) {
        //
    }

}
