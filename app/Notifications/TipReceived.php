<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\SendgridChannel;
use App\Models\Timeline;
use App\Models\Post;
use App\Models\User;
use App\Models\UserSetting;
use App\Interfaces\Tippable;

class TipReceived extends Notification
{
    use NotifyTraits, Queueable;

    public $resource;
    public $actor; // purchaser;
    public $amount;
    protected $settings;

    public function __construct(Tippable $resource, User $actor, array $attrs=[]) {
        $this->resource = $resource;
        $this->actor = $actor;
        if ( array_key_exists('amount', $attrs) ) {
            $this->amount = $attrs['amount'];
        }
        $this->settings = $resource->getPrimaryOwner()->settings;
    }

    public function via($notifiable) {
        $channels =  ['database'];
        if ( $this->isMailChannelEnabled('tip-received', $this->settings) ) {
            $channels[] = $this->getMailChannel();
        }
        return $channels;
    }

    public function toMail($notifiable) {
        return (new MailMessage)
                    ->line($this->actor->name.' sent you a tip!');
    }

    public function toSendgrid($notifiable) {
        return [
            'template_id' => 'new-tip-received',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name, // 'display name'
            ],
            'dtdata' => [
                'sender_name' => $this->actor->name,
                'amount' => nice_currency($this->amount->getAmount()),
                'home_url' => url('/'),
                'referral_url' => url('/referrals'),
                'privacy_url' => url('/privacy'),
                'manage_preferences_url' => url( route('users.showSettings', $notifiable->username) ),
                'unsubscribe_url' => url( route('users.showSettings', $notifiable->username) ),
            ],
        ];
    }

    public function toArray($notifiable) {
        return [
            'resource_type' => $this->resource->getTable(),
            'resource_id' => $this->resource->id,
            'resource_slug' => $this->resource->slug,
            'amount' => $this->amount ?? null,
            'actor' => [ // purchaser
                'username' => $this->actor->username,
                'name' => $this->actor->name,
                'avatar' => $this->actor->avatar->filepath ?? null,
            ],
        ];
    }
}
