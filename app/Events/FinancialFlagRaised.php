<?php

namespace App\Events;

use App\Models\Financial\Flag;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FinancialFlagRaised
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Flag instance
     * @var Flag
     */
    public $flag;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Flag $flag)
    {
        $this->flag = $flag;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('admin-alerts');
    }
}
