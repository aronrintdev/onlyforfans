<?php

namespace App\Events;

use App\Interfaces\Shareable;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccessGranted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    /**
     * Item being granted access to.
     * @var Shareable
     */
    public $item;

    /**
     * Id of user that was granted access.
     * @var string
     */
    public $grantedTo;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Shareable $item, User $grantedTo)
    {
        $this->item = $item->withoutRelations();
        $this->grantedTo = $grantedTo->getKey();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("user-{$this->grantedTo}-access");
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'item_type' => $this->item->getMorphString(),
            'item_id'   => $this->item->getKey(),
        ];
    }
}
