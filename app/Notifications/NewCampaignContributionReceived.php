<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\SendgridChannel;
use App\Models\Campaign;
use App\Models\User;

class NewCampaignContributionReceived extends Notification
{
    use NotifyTraits, Queueable;

    public $campaign;
    public $actor; // subscriber
    public $amount;
    protected $settings;

    public function __construct(Campaign $campaign, User $actor, array $attrs=[])
    {
        $this->campaign = $campaign;
        $this->actor = $actor;
        if ( array_key_exists('amount', $attrs) ) {
            $this->amount = $attrs['amount']; // %FIXME
        }
        $this->settings = $campaign->getPrimaryOwner()->settings;
    }

    public function via($notifiable)
    {
        $channels =  ['database'];
        if ( $this->isMailChannelEnabled('new-campaign-contribution-received', $this->settings) ) {
            $channels[] = $this->getMailChannel();
        }
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line("You received a campaign contribution in the amount of: ".$this->amount->getAmount())
            ->line("From user: Stan Contributer")
            ->line("If you haven't already: Don't forget to go to the referral section to copy and share your code with others. You'll earn up to 5% of their total sales!")
            ->action("Share Referral!", $this->getUrl('referrals'));
    }

    public function toSendgrid($notifiable)
    {
        return [
            'template_id' => 'new-campaign-contribution-received',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name, // 'display name'
            ],
            'dtdata' => [
                'contributor_name' => $this->actor->name,
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
            'campaign_id' => $this->campaign->id,
            'amount' => $this->amount->getAmount(),
            'actor' => [
                'username' => $this->actor->username,
                'name' => $this->actor->name,
                'avatar' => $this->actor->avatar->filepath ?? null,
            ],
        ];
    }
}

