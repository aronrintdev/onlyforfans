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

    // see: https://medium.com/@sirajul.anik/laravel-notifications-part-2-creating-a-custom-notification-channel-6b0eb0d81294
    public function via($notifiable)
    {
        //$channels =  ['database', SendGridChannel::class, ];
        $channels =  ['database', \App\Channels\SendgridChannel::class];
        /* %TODO: uncomment and use SendGridChannel
        $exists = $this->settings->cattrs['notifications']['posts']['new_comment'] ?? false;
        if ( $exists && is_array($exists) && in_array('email', $exists) ) {
            $isGlobalEmailEnabled = ($this->settings->cattrs['notifications']['global']['enabled'] ?? false)
                ? in_array('email', $this->settings->cattrs['notifications']['global']['enabled'])
                : false;
            if ( $isGlobalEmailEnabled ) {
                $channels[] =  'mail';
            }
        }
         */
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

        return [
            'foo' => 'bar',
        ];
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
