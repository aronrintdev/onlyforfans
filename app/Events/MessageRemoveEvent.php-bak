<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\User;

class MessageRemoveEvent  implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatthread;
    public $sender;
    public $receiver;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($chatthreadId, User $sender, User $receiver)
    {
        $this->chatthread = $chatthreadId;
        $this->sender = $sender;
        $this->receiver = $receiver;
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel($this->receiver->id.'-message');
    }
}
