<?php

namespace App\Events;

use App\Interfaces\Purchaseable;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemPurchased implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The Item that was purchased
     *
     * @var Purchaseable
     */
    public $item;

    /**
     * The Purchaser of the item.
     *
     * @var User
     */
    public $purchaser;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Purchaseable $item, User $purchaser)
    {
        $this->item = $item->withoutRelations();
        $this->purchaser = $purchaser->withoutRelations();;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = $this->item->getOwner()->map(function ($owner, $key) {
            return new PrivateChannel("user.{$owner->getKey()}.events");
        });
        $channels->push(new PrivateChannel("user.{$this->purchaser->getKey()}.purchases"));
        return $channels->all();
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
            'item_id' => $this->item->getKey(),
            'purchaser_id' => $this->purchaser->getKey(),
        ];
    }
}
