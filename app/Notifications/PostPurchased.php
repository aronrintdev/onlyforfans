<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Post;
use App\Models\User;

class PostPurchased extends Notification
{
    use Queueable;

    public $post;
    public $purchaser;

    public function __construct(Post $post, User $purchaser)
    {
        $this->post = $post;
        $this->purchaser = $purchaser;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->purchaser->name.' purchased your post');
    }

    public function toArray($notifiable)
    {
        return [
            'resource_type' => $this->post->getTable(),
            'resource_id' => $this->post->id,
            'purchaser' => [
                'username' => $this->purchaser->username,
                'name' => $this->purchaser->name,
            ],
        ];
    }
}
