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
            'name' => $this->first_name.' '.$this->last_name,
            'role' => $this->role,
            'active' => $this->active,
            'pending' => $this->pending,
            'email' => $this->email,
            'last_login_at' => $this->user ? $this->user->last_logged : null,
        ];
    }
}
