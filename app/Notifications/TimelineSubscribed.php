<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Timeline;
use App\Models\User;

class TimelineSubscribed extends Notification
{
    use Queueable;

    public $timeline;
    public $subscriber;

    public function __construct(Timeline $timeline, User $subscriber)
    {
        $this->timeline = $timeline;
        $this->subscriber = $subscriber;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->subscriber->name.' subscribed to your timeline');
    }

    public function toArray($notifiable)
    {
        return [
            'resource_type' => $this->timeline->getTable(),
            'resource_id' => $this->timeline->id,
            'resource_slug' => $this->timeline->slug,
            'subscriber' => [
                'username' => $this->subscriber->username,
                'name' => $this->subscriber->name,
                'avatar' => $this->subscriber->avatar,
            ],
        ];
    }
}
