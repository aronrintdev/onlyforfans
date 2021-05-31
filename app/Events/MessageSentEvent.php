<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\Chatthread;
use App\Models\Chatmessage;
use App\Models\User;

class MessageSentEvent  implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatmessage;

    public function __construct(Chatmessage $chatmessage)
    {
        $this->chatmessage = $chatmessage;
    }


    public function broadcastOn()
    {
        return new PrivateChannel('chatthreads.'.$this->chatmessage->chatthread->id);
    }

    public function broadcastAs()
    {
        return 'chatmessage.sent';
    }
}
