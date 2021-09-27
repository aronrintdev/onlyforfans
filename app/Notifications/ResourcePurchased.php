<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Interfaces\Purchaseable;
//use App\Models\Post;
use App\Models\User;

class ResourcePurchased extends Notification
{
    use NotifyTraits, Queueable;

    public $resource;
    public $actor; // purchaser
    public $amount;

    public function __construct(Purchaseable $resource, User $actor, array $attrs=[])
    {
        $this->resource = $resource;
        $this->actor = $actor;
        if ( array_key_exists('amount', $attrs) ) {
            $this->amount = $attrs['amount'];
        }
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->actor->name.' purchased your post');
    }

    public function toSendgrid($notifiable)
    {
        return []; // %TODO
    }

    public function toArray($notifiable)
    {
        return [
            'resource_type' => $this->resource->getTable(),
            'resource_id' => $this->resource->id,
            'resource_slug' => $this->resource->slug,
            'amount' => $this->amount ?? null,
            'actor' => [ // purchaser
                'username' => $this->actor->username,
                'name' => $this->actor->name,
                'avatar' => $this->actor->avatar->filepath ?? null,
            ],
        ];
    }
}
