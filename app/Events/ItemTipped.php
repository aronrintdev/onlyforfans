<?php

namespace App\Events;

use App\Models\User;
use App\Models\Chatmessage;
use App\Interfaces\Tippable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Http\Resources\Chatmessage as ChatmessageResource;

class ItemTipped implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The item being tipped.
     *
     * @var Tippable
     */
    public $item;

    /**
     * The tipper of the item.
     *
     * @var User
     */
    public $tipper;

    /**
     * The chatmessage this tip is attached to
     * @var Chatmessage|null
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Tippable $item, User $tipper, Chatmessage $message = null)
    {
        $this->item = $item;
        $this->tipper = $tipper;
        $this->message = $message;
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
        $channels->push(new PrivateChannel("user.{$this->tipper->getKey()}.purchases"));
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
            'purchaser_id' => $this->tipper->getKey(),
            'message' => new ChatmessageResource($this->message),
        ];
    }
}
