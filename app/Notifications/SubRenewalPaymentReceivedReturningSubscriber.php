
<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\SendgridChannel;
use App\Interfaces\Subscribable;
use App\Models\Subscription;
use App\Models\User;

class SubRenewalPaymentReceivedReturningSubscriber extends Notification
{
    use NotifyTraits, Queueable;

    public $resource; // thing subscribed to (timeline), from which we can deduce the subcribee
    public $actor; // subscriber
    public $amount;
    protected $settings;

    public function __construct(Subscribable $resource, User $actor, array $attrs=[])
    {
        $this->resource = $resource;
        $this->actor = $actor;
        if ( array_key_exists('amount', $attrs) ) {
            $this->amount = $attrs['amount']; // %FIXME
        }
        $this->settings = $resource->getPrimaryOwner()->settings; // actor = User
    }

    public function via($notifiable)
    {
        $channels =  ['database'];
        $channels[] = $this->getMailChannel();
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line("You received a subscription renewal payment from a returning subscriber in the amount of ".$this->amount->getAmount())
            ->line("From user: ".$this->actor->name)
            ->line("If you haven't already: Don't forget to go to the referral section to copy and share your code with others. You'll earn up to 5% of their total sales!")
            ->action("Share Referral!", $this->getUrl('referrals'));
    }

    public function toSendgrid($notifiable)
    {
        return [
            'template_id' => 'subscription-payment-received-from-returning-subscriber',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name, // 'display name'
            ],
            'dtdata' => [
                'subscriber_name' => $this->actor->name,
                'amount' => nice_currency($this->amount->getAmount()),

                'referral_url' => $this->getUrl('referrals'),
                'home_url' => $this->getUrl('home'),
                'privacy_url' => $this->getUrl('privacy'),
                'manage_preferences_url' => $this->getUrl('manage_preferences', ['username' => $notifiable->username]),
                'unsubscribe_url' => $this->getUrl('unsubscribe', ['username' => $notifiable->username]),
            ],
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'amount' => $this->amount->getAmount(),
            'actor' => [ // commenter
                'username' => $this->actor->username,
                'name' => $this->actor->name,
                'avatar' => $this->actor->avatar->filepath ?? null,
            ],
        ];
    }
}
