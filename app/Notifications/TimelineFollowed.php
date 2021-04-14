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
    use Queueable;

    public $timeline;
    public $follower;

    public function __construct(Likeable $timeline, User $follower)
    {
        $this->timeline = $timeline;
        $this->follower = $follower;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->purchaser->name.' followed your timeline');
    }

    public function toArray($notifiable)
    {
        $follower = [];
        if ( $this->likeable->user ) {
            $follower['username'] = $this->likeable->user->username;
            $follower['name'] = $this->likeable->user->name;
        }
        return [
            'resource_type' => $this->timeline->getTable(),
            'resource_id' => $this->timeline->id,
            'follower' => [
                'username' => $this->follower->username,
                'name' => $this->follower->name,
            ],
        ];
    }
}
