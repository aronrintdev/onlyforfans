<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Chatmessage;
use App\Models\User;

class MessageReceived extends Notification
{
    use Queueable;

    public $chatmessage;
    public $actor; // sender

    public function __construct(Chatmessage $chatmessage, User $actor)
    {
        $this->chatmessage = $chatmessage;
        $this->actor = $actor;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->actor->name.' sent you a message');
    }

    public function toArray($notifiable)
    {
        return [
            'resource_type' => $this->chatmessage->getTable(),
            'resource_id' => $this->chatmessage->id,
            'resource_slug' => $this->chatmessage->chatthread_id,
            'actor' => [ // sender
                'username' => $this->actor->username,
                'name' => $this->actor->name,
                'avatar' => $this->actor->avatar->filepath ?? null,
            ],
        ];
    }
}
