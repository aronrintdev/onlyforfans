<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\SendgridChannel;
use App\Models\Campaign;
use App\Models\User;

class CampaignExpired extends Notification
{
    use NotifyTraits, Queueable;

    public $campaign;

    public function __construct(Campaign $campaign, array $attrs=[])
    {
        $this->campaign = $campaign;
    }

    public function via($notifiable)
    {
        $channels =  ['database'];
        $channels[] = $this->getMailChannel(); // add by default (not configurable in settings as of yet)
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)->line("Not used");
    }

    public function toSendgrid($notifiable)
    {
        return [
            'template_id' => 'campaign-expired',
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
