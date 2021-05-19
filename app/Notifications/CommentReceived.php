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
    public $actor; // commenter;
    protected $settings;

    public function __construct(Commentable $resource, User $actor, array $attrs=[])
    {
        $this->resource = $resource;
        $this->actor = $actor;
        $this->settings = $resource->getPrimaryOwner()->settings; // resource ~= commentable
    }

    public function via($notifiable)
    {
        $channels =  ['database'];
        $exists = $this->settings->cattrs['notifications']['posts']['new_comment'] ?? false;
        if ( $exists && is_array($exists) && in_array('email', $exists) ) {
            $isGlobalEmailEnabled = ($this->settings->cattrs['notifications']['global']['enabled'] ?? false)
                ? in_array('email', $this->settings->cattrs['notifications']['global']['enabled'])
                : false;
            if ( $isGlobalEmailEnabled ) {
                $channels[] =  'mail';
            }
        }
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('You received a comment from '.$this->actor->name)
                    ->action('Notification Action', url('/'));
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
