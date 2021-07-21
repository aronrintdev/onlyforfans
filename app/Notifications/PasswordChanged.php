<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\SendgridChannel;
use App\Models\User;

class PasswordChanged extends Notification
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
            ->line('This email is to confirm your recent password change. No further action is necessary.')
            ->line('If you did not recently change your password, please contact our support team.');
    }

    public function toSendgrid($notifiable)
    {
        return [
            'template_id' => 'password-changed-confirmation',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name, // 'display name'
            ],
            'dtdata' => [
                'user_name' => $this->actor->name,

                'home_url' => $this->getUrl('home'),
                'login_url' => $this->getUrl('login'),
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
