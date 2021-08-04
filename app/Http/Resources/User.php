<?php
namespace App\Http\Resources;

//use App\Enums\PostTypeEnum;
use App\Models\User as UserModel;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'username' => $this->username,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'is_verified' => $this->is_verified,
            'email_verified_at' => $this->email_verified_at,
            'last_logged' => $this->last_logged,
            'verified_status' => $this->verified_status,
            'created_at' => $this->created_at,
            'timeline' => $this->timeline,
        ];
    }
}
