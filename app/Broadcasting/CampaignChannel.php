<?php

namespace App\Broadcasting;

use App\Models\User;

/**
 * Channel for events for a specific campaign
 */
class CampaignChannel
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
     * @return array|bool
     */
    public function join(User $user, $campaignId)
    {
        // If user is logged in.
        return true;
    }
}
