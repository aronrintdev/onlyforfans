<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\SendgridChannel;
use App\Models\Campaign;
use App\Models\User;

class CampaignGoalReached extends Notification
{
    use NotifyTraits, Queueable;

    public $campaign;
    public $amount;
    protected $settings;

    public function __construct(Campaign $campaign, array $attrs=[])
    {
        $this->campaign = $campaign;
        if ( array_key_exists('amount', $attrs) ) {
            $this->amount = $attrs['amount']->getAmount(); // %FIXME
        }
        $this->settings = $campaign->getPrimaryOwner()->settings;
    }

    public function via($notifiable)
    {
        $channels =  ['database'];
        if ( $this->isMailChannelEnabled('campaign-goal-reached', $this->settings) ) {
            $channels[] = $this->getMailChannel();
        }
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line("Congrats! Your campaign reached it's goal in the amount of: ".$this->amount)
            ->line("If you haven't already: Don't forget to go to the referral section to copy and share your code with others. You'll earn up to 5% of their total sales!");
    }

    public function toSendgrid($notifiable)
    {
        return [
            'template_id' => 'campaign-goal-reached',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name, // 'display name'
            ],
            'dtdata' => [
                'user_name' => $this->notifiable->name,
                'amount' => nice_currency($this->amount),

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
            'amount' => $this->amount,
            'user_name' => $this->notifiable->name,
        ];
    }
}
