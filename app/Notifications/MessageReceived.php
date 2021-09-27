<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Channels\SendgridChannel;
use App\Models\User;
use App\Models\Chatmessage;

class MessageReceived extends Notification
{
    use NotifyTraits, Queueable;

    public $message; // [chatmessages] record
    public $sender;
    protected $settings;

    public function __construct(Chatmessage $message, User $sender, array $attrs=[])
    {
        $this->message = $message;
        $this->sender = $message->sender;
        if ( array_key_exists('amount', $attrs) ) {
            $this->amount = $attrs['amount']; // %FIXME
        }
        $this->settings = $message->getPrimaryOwner()->settings; // should be the sender
    }

    public function via($notifiable)
    {
        $channels =  ['database'];
        if ( $this->isMailChannelEnabled('new-message-received', $this->settings) ) {
            $channels[] = $this->getMailChannel();
        }
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line("Hi " . $notifiable->name)
            ->line("You have 1 new unread message from ".$this->sender->name)
            ->line($this->message->mcontent)
            ->action("Reply", $this->getUrl('reply_to_message', ['chatthread_id' => $this->message->chatthread_id]));
    }

    public function toSendgrid($notifiable)
    {
        return [
            'template_id' => 'new-message-received',
            'to' => [
                'email' => $notifiable->email,
                'name' => $notifiable->name, // 'display name'
            ],
            'dtdata' => [
                'sender_name' => $this->sender->name,
                'sender_avatar_url' => $this->sender->avatar->filepath,
                'receiver_name' => $notifiable->name,
                'message_preview' => $this->message->mcontent,
                'number_of_messages' => 1,

                'reply_url' => $this->getUrl('reply_to_message', ['chatthread_id' => $this->message->chatthread_id]),
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
            'chatmessage_id' => $this->message->id,
            'chatmessage_preview' => $this->message->mcontent,
            'reciever_name' => $notifiable->name,
            'sender' => [
                'username' => $this->sender->username,
                'name' => $this->sender->name,
                'avatar' => $this->sender->avatar->filepath ?? null,
            ],
        ];
    }
}
