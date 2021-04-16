<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Comment;
use App\Models\User;
use App\Interfaces\Commentable;

class CommentReceived extends Notification
{
    use Queueable;

    public $resource;
    public $actor; // purchaser;

    //public function __construct(Timeline $timeline, User $purchaser)
    public function __construct(Commentable $resource, User $actor, array $attrs=[])
    {
        $this->resource = $resource;
        $this->actor = $actor;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->actor->name.' sent you a comment!');
    }

    public function toArray($notifiable)
    {
        return [
            'resource_type' => $this->resource->getTable(),
            'resource_id' => $this->resource->id,
            'resource_slug' => $this->resource->slug,
            'actor' => [ // commenter
                'username' => $this->actor->username,
                'name' => $this->actor->name,
                'avatar' => $this->actor->avatar->filepath ?? null,
            ],
        ];
    }
}
