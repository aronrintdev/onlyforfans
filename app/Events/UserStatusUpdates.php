<?php

namespace App\Events;

use Cmgmyr\Messenger\Models\Message;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class UserStatusUpdates extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $userId;
    public $status;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($userId, $status)
    {
        $this->userId = $userId;
        $this->status = $status;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['user-status-updates'];
    }
}
