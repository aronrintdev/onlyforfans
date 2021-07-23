<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\SendgridChannel;
use App\Models\Comment;
use App\Models\User;
use App\Interfaces\Commentable;

class CommentReceived extends Notification
{
    use NotifyTraits, Queueable;

    public $resource;
    public $actor; // commenter;
    protected $settings;

    public function __construct(Commentable $resource, User $actor, array $attrs=[])
    {
        $this->resource = $resource;
        $this->actor = $actor;
        $this->settings = $resource->getPrimaryOwner()->settings; // resource ~= commentable
    }

    // see: https://medium.com/@sirajul.anik/laravel-notifications-part-2-creating-a-custom-notification-channel-6b0eb0d81294
    public function via($notifiable)
    {
        $channels =  ['database'];
        if ( $this->isMailChannelEnabled('tip-received', $this->settings) ) {
            $channels[] = $this->getMailChannel();
        }
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('You received a comment from '.$this->actor->name)
            ->action('Read Comment', url('/'));
    }

    public function toSendgrid($notifiable)
    {

        $data = [
            'template_id' => 'new-comment-received',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name, // 'display name'
            ],
            'dtdata' => [
                'sender_name' => $this->actor->name,
                'receiver_name' => $notifiable->name,
                'preview' => $this->resource->slug,

                'home_url' => $this->getUrl('home'),
                'login_url' => $this->getUrl('login'),
                'privacy_url' => $this->getUrl('privacy'),
                'manage_preferences_url' => $this->getUrl('manage_preferences', ['username' => $notifiable->username]),
                'unsubscribe_url' => $this->getUrl('unsubscribe', ['username' => $notifiable->username]),
                'referral_url' => $this->getUrl('referrals'),
            ],
        ];
        return $data;
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
