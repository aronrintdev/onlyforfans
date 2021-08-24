<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

class AchAccount extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $default = Auth::user()->settings->cattrs['default_payout_method'] ?? null;

        return [
            'id'                    => $this->id,
            'account_id'            => $this->account ? $this->account->id : '',
            'type'                  => $this->type,
            'default'               => $this->id === $default ? true : false,
            'name'                  => $this->name,
            'beneficiary_name'      => $this->beneficiary_name,
            'bank_name'             => $this->bank_name,
            'routing_number'        => $this->routing_number,
            'account_number_last_4' => Str::substr($this->account_number, -4),
            'account_type'          => $this->account_type,
            'currency'              => $this->currency,
            'created_at'            => $this->created_at,
            // 'metadata' => $this->metadata,
        ];
    }
}
