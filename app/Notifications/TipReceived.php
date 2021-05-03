<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Timeline;
use App\Models\Post;
use App\Models\User;
use App\Models\UserSetting;
use App\Interfaces\Tippable;

class TipReceived extends Notification
{
    use Queueable;

    public $resource;
    public $actor; // purchaser;
    public $amount;
    protected $settings;

    //public function __construct(Timeline $timeline, User $purchaser)
    public function __construct(Tippable $resource, User $actor, array $attrs=[])
    {
        $this->resource = $resource;
        $this->actor = $actor;
        if ( array_key_exists('amount', $attrs) ) {
            $this->amount = $attrs['amount'];
        }
        $this->settings = request()->user()->settings;
    }

    public function via($notifiable)
    {
        $channels =  ['database'];
        $exists = $this->settings->cattrs['notifications']['income']['new_tip'] ?? false;
        if ( $exists && is_array($exists) && in_array('email', $exists) ) {
            $channels[] =  ['mail'];
        }
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->actor->name.' sent you a tip!');
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
