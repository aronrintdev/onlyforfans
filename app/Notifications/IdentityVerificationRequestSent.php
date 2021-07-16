<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Verifyrequest;

class IdentityVerificationRequestSent extends Notification
{
    use Queueable;

    public $vr;
    public $requester;

    public function __construct(Verifyrequest $vr, User $requester) {
        $this->vr = $vr;
        $this->requester = $requester;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Your identity verification request has been received. Please look for a text on your phone for the next step in the process');
    }

    public function toArray($notifiable)
    {
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
