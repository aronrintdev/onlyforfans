<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Campaign extends JsonResource
{
    public function toArray($request)
    {
        return [
            'type' => $this->type,
            'has_new' => $this->has_new,
            'has_expired' => $this->has_expired,
            'targeted_customer_group' => $this->targeted_customer_group,
            'subscriber_count' => $this->subscriber_count,
            'offer_days' => $this->offer_days,
            'discount_percent' => $this->discount_percent,
            'trial_days' => $this->trial_days,
            'message' => $this->message,
            'created_at' => $this->created_at,
        ];
    }
}
