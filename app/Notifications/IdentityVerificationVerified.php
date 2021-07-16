<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Verifyrequest;

class IdentityVerificationVerified extends Notification
{
    use Queueable;

    public $vr;
    public $requester;

    public function __construct(Verifyrequest $vr, User $requester) {
        $this->vr = $vr;
        $this->requester = $requester;
    }

    public function via($notifiable) {
        return ['database', 'mail'];
    }

    public function toMail($notifiable) {
        $url = url('/settings/verify');
        return (new MailMessage)
                ->line('Congratulations! Your identity verification is complete. Please follow the link below to continue setting up your account to receive payments!')
                ->action('Get Paid!', $url);
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
