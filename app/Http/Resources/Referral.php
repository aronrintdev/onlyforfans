<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Referral as ReferralModel;

class Referral extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = ReferralModel::find($this->id);
        $fobj = $this->referral;
        return [
            'id' => $this->id,
            'referral_id' => $this->referral_id,
            'referral' => $fobj,
            'creator' => $this->referral->user,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
        ];
    }
}
