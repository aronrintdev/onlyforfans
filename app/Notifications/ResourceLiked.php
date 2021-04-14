<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Interfaces\Likeable;
use App\Models\User;

// Resource can be Post, Comment, Mediafile, etc
class ResourceLiked extends Notification
{
    use Queueable;

    public $likeable;
    public $liker;

    public function __construct(Likeable $likeable, User $liker)
    {
        $this->likeable = $likeable; // resource liked: Post, Comment, etc.
        $this->liker = $liker;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('You received a like from '.$this->liker->name)
                    ->action('Notification Action', url('/'));
    }

    public function toArray($notifiable)
    {
        return [
            'resource_type' => $this->likeable->getTable(),
            'resource_id' => $this->likeable->id,
            'liker' => [
                'username' => $this->liker->username,
                'name' => $this->liker->name,
            ],
        ];
    }
}
