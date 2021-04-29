<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Auth;
use App\Models\Casts\Money as CastsMoney;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Subscription as SubscriptionModel;

class Subscription extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'type' => $this->subscribable_type,
            'period' => $this->period,
            'period_interval' => $this->period_interval,
            'price' => CastsMoney::doSerialize($this->price),
            'currency' => $this->currency,
            'access_level' => $this->access_level,
            'active' => $this->active,
            'canceled' => $this->canceled_at,
        ];

        if ( $this->isOwner(Auth::user()) ) {
            $data['payment_method'] = new PaymentMethod($this->account);
        }

        if ($this->subscribable_type === 'timelines') {
            $data['for'] = new Timeline($this->subscribable);
        }

        return $data;
    }
}