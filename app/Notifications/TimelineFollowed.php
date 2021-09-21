<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Timeline;
use App\Models\User;

class TimelineFollowed extends Notification
{
    use NotifyTraits, Queueable;

    public $timeline;
    public $actor; // follower

    public function __construct(Timeline $timeline, User $actor)
    {
        $this->timeline = $timeline;
        $this->actor = $actor;
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        $channels[] = $this->getMailChannel();
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->actor->name.' followed your timeline');
    }

    public function toSendgrid($notifiable)
    {
        $data = [];
        return $data;
    }

    public function toArray($notifiable)
    {
        return [
            'resource_type' => $this->timeline->getTable(),
            'resource_id' => $this->timeline->id,
            'resource_slug' => $this->timeline->slug,
            'actor' => [ // follower
                'username' => $this->actor->username,
                'name' => $this->actor->name,
                'avatar' => $this->actor->avatar->filepath ?? null,
            ],
        ];
    }
}
