<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\SendgridChannel;
use App\Models\User;

class StaffSettingsChanged extends Notification
{
    use NotifyTraits, Queueable;

    public $actor;
    public $manager;
    public $settings;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $manager, User $actor, array $attrs=[])
    {
        $this->manager = $manager;
        $this->actor = $actor;
        $this->settings = $attrs;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels =  ['database'];
        $channels[] = $this->getMailChannel();
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->actor->name.' has updated your management percentage to '.$this->settings['earnings']['value'].'%');
    }

    public function toSendgrid($notifiable) {
        $data = [
            'template_id' => 'change-percentage-of-gross-earnings',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name,
            ],
            'dtdata' => [
                'manager_name' => $this->manager->name,
                'username' => $this->actor->name,
                'percent' => $this->settings['earnings']['value'],
                'home_url' => $this->getUrl('home'),
                'privacy_url' => $this->getUrl('privacy'),
                'manage_preferences_url' => $this->getUrl('manage_preferences', ['username' => $notifiable->username]),
                'unsubscribe_url' => $this->getUrl('unsubscribe', ['username' => $notifiable->username]),
            ],
        ];
        return $data;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'settings' => $this->settings,
            'actor' => [
                'username' => $this->actor->username,
                'id' => $this->actor->id,
                'name' => $this->actor->name,
                'avatar' => $this->actor->avatar->filepath ?? null,
            ],
        ];
    }
}
