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
    public $actor; // liker
    protected $settings;

    public function __construct(Likeable $likeable, User $actor)
    {
        $this->likeable = $likeable; // resource liked: Post, Comment, etc.
        $this->actor = $actor;
        $this->settings = request()->user()->settings;
    }

    public function via($notifiable)
    {
        //return ['database', 'mail'];
        $channels =  ['database'];
        // HERE TUESDAY
        $exists = $this->settings->cattrs['notifications']['posts']['new_like'] ?? false;
        //dd($exists,  $this->settings->cattrs['notifications']['posts']['new_like'] ?? false);
        if ( $exists && is_array($exists) && in_array('email', $exists) ) {
            $channels[] =  ['mail'];
        }
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('You received a like from '.$this->actor->name)
                    ->action('Notification Action', url('/'));
    }

    public function toArray($notifiable)
    {
        return [
            'resource_type' => $this->likeable->getTable(),
            'resource_id' => $this->likeable->id,
            'resource_slug' => $this->likeable->slug ?? null,
            'actor' => [ // liker
                'username' => $this->actor->username,
                'name' => $this->actor->name,
                'avatar' => $this->actor->avatar->filepath ?? null,
            ],
        ];
    }
}
