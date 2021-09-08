<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\User;
use App\Models\Post;


class UserTagged extends Notification
{
    use Queueable;

    public $post;
    public $actor;

    public function __construct(Post $post, User $actor)
    {
        $this->post = $post;
        $this->actor = $actor;
    }

    public function via($notifiable)
    {
        $channels =  ['database'];
        return $channels;
    }

    public function toArray($notifiable)
    {
        return [
            'resource_id' => $this->post->id,
            'resource_slug' => $this->post->slug,
            'actor' => [
                'username' => $this->actor->username,
                'name' => $this->actor->name,
                'avatar' => $this->actor->avatar->filepath ?? null,
            ],
        ];
    }
}
