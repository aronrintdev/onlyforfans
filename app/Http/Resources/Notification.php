<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Notification as NotificationModel;
use App\Models\User;

class Notification extends JsonResource
{
    public function toArray($request)
    {
        if (array_key_exists('actor', $this->data)) {
            $data = $this->data['actor'];
        } else if (array_key_exists('sender', $this->data)) {
            $data = $this->data['sender'];
        } else if (array_key_exists('requester', $this->data)) {
            $data = $this->data['requester'];
        }
        $user = User::where('username', $data['username'])->first();
        if ($user) {
            $user->slug = $user->timeline->slug;
        }

        return [
            'id' => $this->id,
            'notifiable_id' => $this->notifiable_id,
            'notifiable_type' => $this->notifiable_type,
            'read_at' => $this->read_at,
            'is_read' => !is_null($this->read_at),
            'type' => $this->type,
            'data' => $this->data,
            'user' => $user,
            'created_at' => $this->created_at,
        ];
    }
}
