<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\SendgridChannel;
use App\Models\User;

class PasswordReset extends Notification
{
    use NotifyTraits, Queueable;

    public $actor;
    protected $settings;

    public function __construct(User $user)
    {
        $this->actor = $actor;
        $this->settings = $actor->settings; // actor = User
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
            ->line('Hi '.$this->actor->name)
            ->line('Please click the link below within 24 hours to reset your password.')
            ->action('Notification Action', $this->getUrl('password_reset'));
    }

    public function toSendgrid($notifiable)
    {
        return [
            'template_id' => 'password-reset',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name, // 'display name'
            ],
            'dtdata' => [
                'user_name' => $this->actor->name,

                'home_url' => $this->getUrl('home'),
                'password_reset_url' => $this->getUrl('password_reset'),
                'privacy_url' => $this->getUrl('privacy'),
                'manage_preferences_url' => $this->getUrl('manage_preferences', ['username' => $notifiable->username]),
                'unsubscribe_url' => $this->getUrl('unsubscribe', ['username' => $notifiable->username]),
            ],
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'actor' => [ // commenter
                'username' => $this->actor->username,
                'name' => $this->actor->name,
                'avatar' => $this->actor->avatar->filepath ?? null,
            ],
        ];
    }
}
