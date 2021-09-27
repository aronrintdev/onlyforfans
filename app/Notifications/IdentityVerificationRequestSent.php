<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Verifyrequest;

class IdentityVerificationRequestSent extends Notification // aka 'pending'
{
    use NotifyTraits, Queueable;

    public $vr;
    public $requester;

    public function __construct(Verifyrequest $vr, User $requester) {
        $this->vr = $vr;
        $this->requester = $requester;
    }

    public function via($notifiable) {
        $channels =  ['database'];
        $channels[] = $this->getMailChannel(); // verification rejected should always be enabled!
        return $channels;
    }

    public function toMail($notifiable) {
        return (new MailMessage)
                ->line('Your identity verification request has been received. Please look for a text on your phone to complete the final step in the process.');
    }

    public function toSendgrid($notifiable) {
        return [
            'template_id' => 'id-verification-pending',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name, // 'display name'
            ],
            'dtdata' => [
                'user_name' => $notifiable->name,
                'home_url' => $this->getUrl('home'),
                'verify_url' => $this->getUrl('verify'),
                'login_url' => $this->getUrl('login'),
                'referral_url' => $this->getUrl('referrals'),
                'privacy_url' => $this->getUrl('privacy'),
                'manage_preferences_url' => $this->getUrl('manage_preferences', ['username' => $notifiable->username]),
                'unsubscribe_url' => $this->getUrl('unsubscribe', ['username' => $notifiable->username]),
            ],
        ];
    }

    public function toArray($notifiable) {
        return [
            'guid' => $this->vr->guid,
            'vstatus' => $this->vr->vstatus ?? 'none',
            'requester' => [ // person being verified
                'username' => $this->requester->username,
                'name' => $this->requester->name,
                'avatar' => $this->requester->avatar->filepath ?? null,
            ],
        ];
    }
}
