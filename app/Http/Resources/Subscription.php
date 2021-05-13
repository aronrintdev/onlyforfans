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
            'subscribable_type' => $this->subscribable_type,
            'subscribable_id' => $this->subscribable_id,
            'user_id' => $this->user_id,
            'period' => $this->period,
            'period_interval' => $this->period_interval,
            'price' => CastsMoney::doSerialize($this->price),
            'currency' => $this->currency,
            'access_level' => $this->access_level,
            'active' => $this->active,
            'canceled_at' => $this->canceled_at,

            'type' => $this->subscribable_type, // %FIXME: DEPRECATE, follow conventions setup elsehwere (use subscribable_type above)
            'canceled' => $this->canceled_at, // %FIXME: DEPRECATE, follow conventions setup elsehwere
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
