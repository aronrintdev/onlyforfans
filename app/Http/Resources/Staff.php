<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Staff extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->first_name.' '.$this->last_name,
            'email' => $this->email,
            'role' => $this->role,
            'active' => $this->active,
            'pending' => $this->pending,
            'owner_id' => $this->owner_id,
            'user_id' => $this->user_id,
            'creator_id' => $this->creator_id,
            'settings' => $this->settings ?? [],
            'last_login_at' => $this->user ? $this->user->last_logged : null,
            'created_at' => $this->created_at,
        ];
    }
}
