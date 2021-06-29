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
        return new PresenceChannel('chatthreads.' . $this->chatmessage->chatthread->id);
    }

    public function broadcastAs()
    {
        return 'chatmessage.sent';
    }

    /*
    public function broadcastWith()
    {
        return [
            'msg' => $this->chatmessage->mcontent,
            'chatthread_id' => $this->chatmessage->chatthread_id,
            'created_at' => $this->chatmessage->created_at,
            'sender_id' => $this->chatmessage->sender->id,
            'sender_name' => $this->chatmessage->sender->name,
        ];
    }
     */
}
