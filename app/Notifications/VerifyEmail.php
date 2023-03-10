<?php
namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmail extends Notification
{
    use NotifyTraits, Queueable;

    public $actor;
    public $url;
    protected $settings;

    public function __construct(User $actor, string $url)
    {
        $this->actor = $actor;
        $this->url = $url;
        $this->settings = $actor->settings; // actor = User
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        $channels[] = $this->getMailChannel();
        return $channels;
    }

    public function toMail()
    {
        return (new MailMessage)
            ->line('Hi' . $this->actor->name . ', Welcome to allfans.com')
            ->line('Verify your email address')
            ->action('Verify Email', $this->url);
    }

    public function toSendgrid($notifiable)
    {
        Log::debug('VerifyEmail toSendgrid', [
            'notifiable' => $notifiable,
            'actor->name' => $this->actor->name,
            'url' => $this->url,
        ]);
        return [
            'template_id' => 'verify-email',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name,
            ],
            'dtdata' => [
                'user_name' => $this->actor->name,
                'verify_url' => $this->url,
            ],
        ];
    }

    public function toArray()
    {
        return [
            'actor' => [
                'username' => $this->actor->username,
                'name' => $this->actor->name,
            ]
        ];
    }

}
