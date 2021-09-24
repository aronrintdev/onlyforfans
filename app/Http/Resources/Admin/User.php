<?php

namespace App\Http\Resources\Admin;

//use App\Enums\PostTypeEnum;
use App\Models\User as UserModel;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    public function toArray($request)
    {
        $model = UserModel::with(['timeline', 'settings'])->find($this->id);


        return [
            'id' => $this->id,
            'email' => $this->email,
            'username' => $this->username,
            // 'firstname' => $this->firstname,
            // 'lastname' => $this->lastname,
            'is_verified' => $this->is_verified,
            'email_verified_at' => $this->email_verified_at,
            'payments_disabled' => isset($model->settings->cattrs['disable_payments']) ? true : false,
            'last_logged' => $this->last_logged,
            'verified_status' => $this->verified_status,
            //'campaign' => $this->campaign,
            'created_at' => $this->created_at,
            'timeline' => $this->timeline,
        ];
    }
}
