<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

//use NotificationChannels\SendGrid\SendGridChannel;
//use Illuminate\Notifications\Messages\SendGridMessage;
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
            ->action('Notification Action', url('/'));
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
                'home_url' => url('/'),
                'referral_url' => url('/referrals'),
                'privacy_url' => url('/privacy'),
                'manage_preferences_url' => url( route('users.showSettings', $notifiable->username) ),
                'unsubscribe_url' => url( route('users.showSettings', $notifiable->username) ),
            ],
        ];
        return $data;
        /*
        return (new SendGridMessage('d-c81aa70638ac40f5a33579bf425aa591'))
            ->payload($data)
            ->from('info@allfans.com', 'AllFans Support')
            //->to('receiver1@example.com', 'Example Receiver');
            ->to('peter+campaign-goal-template-active@peltronic.com', 'Peter Receiver');
         */
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

        /*
        $data = [
            'first_name' => $this->actor->firstname,
            'last_name' => $this->actor->lastname,
            'email' => 'recipient@example.com',
            'address_line_1' => '',
            'city' => '',
            'state_province_region' => '',
            'postal_code' => '',
            'verify_url' => url('/verify'),
            'type_of_payment' => 'tip payment',
            'amount' => '$3.26',
            'display_name' => $this->actor->name,

            'mail_settings' =>  [
                'sandbox_mode' => [
                    'enable' => true
                ]
            ]
        ];
         */
