<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Vault;
use App\Models\Vaultfolder;
use App\Models\User;

class VaultfileShareSent extends Notification
{
    use Queueable;

    public $resource;
    public $sender;

    public function __construct(Vaultfolder $resource, User $sender, array $attrs=[])
    {
        $this->resource = $resource;
        $this->sender = $sender;
    }

    public function via($notifiable)
    {
        // %TODO: wire to settings
        $channels = ['database', 'mail'];
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line($this->sender->name.' has shared files with you. Please visit your vault to accept the share')
                    ->action('Accept Share', route('vault.dashboard'));
    }

    public function toArray($notifiable)
    {
        return [
            'resource_type' => $this->resource->getTable(),
            'resource_id' => $this->resource->id,
            'resource_slug' => $this->resource->slug,
            'sender' => [
                'username' => $this->sender->username,
                'name' => $this->sender->name,
                'avatar' => $this->sender->avatar->filepath ?? null,
            ],
        ];
    }
}
