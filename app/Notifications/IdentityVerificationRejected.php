<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use App\Channels\SendgridChannel;

use App\Models\User;
use App\Models\Verifyrequest;

class IdentityVerificationRejected extends Notification
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
                    ->line('Your identity verification could not be processed. Please contact AllFans support');
    }

    public function toSendgrid($notifiable) {
        return [
            'template_id' => 'id-verification-rejected',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name, // 'display name'
            ],
            'dtdata' => [
                'home_url' => $this->getUrl('home'),
                'login_url' => $this->getUrl('login'),
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
