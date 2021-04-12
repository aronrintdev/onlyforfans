<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Interfaces\Likeable;
//use App\Models\Post;

// Resource can be Post, Comment, Mediafile, etc
class ResourceLiked extends Notification
{
    use Queueable;

    public $likeable;

    public function __construct(Likeable $likeable)
    {
        $this->likeable = $likeable;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'resource_type' => $this->likeable->getTable(),
            'id' => $this->likeable->id,
        ];
    }
}
