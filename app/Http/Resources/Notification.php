<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Notification as NotificationModel;

class Notification extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();

        return [
            'id' => $this->id,
            'notifiable_id' => $this->notifiable_id,
            'notifiable_type' => $this->notifiable_type,
            'type' => $this->type,
            'data' => $this->data,
            'created_at' => $this->created_at,
        ];
    }
}
