<?php

namespace App\Events;

use App\Models\User;
use App\Interfaces\Shareable;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AccessRevoked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Item that access is being revoked from.
     * @var Shareable
     */
    public $item;

    /**
     * Id of user access is being revoked from.
     * @var string
     */
    public $revokedFrom;

    /**
     * String representation of reason for revoke that is displayed.
     * @var string|null
     */
    public $reason;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Shareable $item, User $revokedFrom, string $reason = null)
    {
        $this->item = $item->withoutRelations();
        $this->revokedFrom = $revokedFrom->getKey();
        $this->reason = $reason;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("user-{$this->revokedFrom}-access");
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
            'reason'    => $this->reason,
        ];
    }
}
