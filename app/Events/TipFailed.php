<?php

namespace App\Events;

use App\Interfaces\Tippable;
use App\Models\Financial\Account;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TipFailed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The Item that was purchased
     *
     * @var Tippable
     */
    public $item;

    /**
     * The account purchasing the item.
     *
     * @var Account
     */
    public $account;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Tippable $item, Account $account)
    {
        $this->item = $item->withoutRelations();
        $this->account = $account->withoutRelations();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = $this->account->getOwner()->map(function ($owner, $key) {
            return new PrivateChannel("user-{$owner->getKey()}-purchases");
        });
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
            'account_id' => $this->account->getKey(),
        ];
    }
}
