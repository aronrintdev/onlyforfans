<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
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
        return [
            'id'                    => $this->getKey(),
            'account_id'            => $this->account->getKey(),
            'type'                  => $this->type,
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
