<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\SendgridChannel;
use App\Models\User;

// %TODO: currently using stub code - this needs to be completed once we build out the referral functionality
class NewReferralReceived extends Notification
{
    use NotifyTraits, Queueable;

    public $referral;
    public $actor; // subscriber
    protected $settings;

    protected $recipientName; // person being referred
    protected $earningPercent; 

    public function __construct($referral, User $actor, array $attrs=[])
    {
        $this->referral = $referral;
        $this->actor = $actor;
        $this->settings = $referral->getPrimaryOwner()->settings; // actor = User

        $this->recipientName = $this->referral->recipient->name;
        $this->earningPercent = $this->referral->amount->getAmount();
    }

    public function via($notifiable)
    {
        $channels =  ['database'];
        if ( $this->isMailChannelEnabled('new-referral-received', $this->settings) ) {
            $channels[] = $this->getMailChannel();
        }
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line("Congrats! You just received a new referral.")
            ->line("You just referred user: ".$this->recipientName)
            ->line("You'll continue to earn ".$this->earningPercent."% of ".$this->recipientName."'s sales as long as they are an active member.")
            ->line("Keep up the great work!");
    }

    public function toSendgrid($notifiable)
    {
        return [
            'template_id' => 'new-referral-received',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name, // 'display name'
            ],
            'dtdata' => [
                'referred_name' => $this->recipientName,
                'percentage_earned' => $this->earningAmount().'%',

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
            'referral_id' => $this->referral->id,
            'earning_mount' => $this->earningAmount,
            'actor' => [
                'username' => $this->actor->username,
                'name' => $this->actor->name,
                'avatar' => $this->actor->avatar->filepath ?? null,
            ],
        ];
    }
}


