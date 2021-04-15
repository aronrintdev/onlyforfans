<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Timeline;
use App\Models\User;

class TimelineTipped extends Notification
{
    use Queueable;

    public $timeline;
    public $purchaser;

    public function __construct(Timeline $timeline, User $purchaser)
    {
        $this->timeline = $timeline;
        $this->purchaser = $purchaser;
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

    public function toArray($notifiable)
    {
        return [
            'resource_type' => $this->timeline->getTable(),
            'resource_id' => $this->timeline->id,
            'resource_slug' => $this->timeline->slug,
            'purchaser' => [
                'username' => $this->purchaser->username,
                'name' => $this->purchaser->name,
                'avatar' => $this->purchaser->avatar,
            ],
        ];
    }
}
