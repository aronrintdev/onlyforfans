<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Timeline;
use App\Models\Post;
use App\Models\User;

class PostTipped extends Notification
{
    use NotifyTraits, Queueable;

    public $post;
    public $purchaser;
    public $amount;

    public function __construct(Post $post, User $purchaser, array $attrs=[])
    {
        throw new \Exception('deprecated, use TipReceived');
        $this->post = $post;
        $this->purchaser = $purchaser;
        if ( array_key_exists('amount', $attrs) ) {
            $this->amount = $attrs['amount'];
        }
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->purchaser->name.' sent you a tip!');
    }

    public function toSendgrid($notifiable)
    {
        return []; // %TODO
    }

    public function toArray($notifiable)
    {
        return [
            'resource_type' => $this->post->getTable(),
            'resource_id' => $this->post->id,
            'resource_slug' => $this->post->slug,
            'amount' => $this->amount ?? null,
            'actor' => [ // purchaser
                'username' => $this->purchaser->username,
                'name' => $this->purchaser->name,
                'avatar' => $this->purchaser->avatar->filepath ?? null,
            ],
        ];
    }
}
