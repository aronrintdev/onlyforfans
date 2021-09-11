<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Campaign extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'active' => $this->active,
            'has_new' => $this->has_new,
            'has_expired' => $this->has_expired,
            'targeted_customer_group' => $this->targeted_customer_group,
            'subscriber_count' => $this->subscriber_count,
            'is_subscriber_count_unlimited' => $this->is_subscriber_count_unlimited,
            'offer_days' => $this->offer_days,
            'discount_percent' => $this->discount_percent,
            'trial_days' => $this->trial_days,
            'message' => $this->message,
            'created_at' => $this->created_at,
        ];
    }
}
