<?php

namespace App\Events\Campaign;

use App\Models\Campaign;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Updated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The campaign being updated
     * @var Campaign
     */
    public $campaign;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("campaign.{$this->campaign->id}");
    }

    public function broadcastWith()
    {
        return [
            'remaining_count' => $this->campaign->remaining_count,
        ];
    }
}
