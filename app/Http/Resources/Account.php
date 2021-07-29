<?php
namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

use App\Models\Account as AccountModel;

class Account extends JsonResource
{
    public function toArray($request)
    {
        //$default = Auth::user()->settings->cattrs['default_payout_method'] ?? null;

        return [
            'id'                      => $this->getKey(),
            'system'                  => $this->system,
            'owner_type'              => $this->owner_type,
            'owner_id'                => $this->owner_id,
            'name'                    => $this->name,
            'type'                    => $this->type,
            'currency'                => $this->currency,
            'balance'                 => $this->balance,
            'balance_last_updated_at' => $this->balance_last_updated_at,
            'pending'                 => $this->pending,
            'pending_last_updated_at' => $this->pending_last_updated_at,
            'resource_type'           => $this->resource_type,
            'resource_id'             => $this->resource_id,
            'verified'                => $this->verified,
            'can_make_transactions'   => $this->can_make_transactions,
            'created_at'              => $this->created_at,
        ];
    }
}
