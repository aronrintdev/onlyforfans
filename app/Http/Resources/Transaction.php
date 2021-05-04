<?php

namespace App\Http\Requests;

use Illuminate\Http\Resources\Json\JsonResource;

class Transaction extends JsonResource
{
    //

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'credit_amount' => $this->credit_amount,
            'debit_amount' => $this->debit_amount,
            'currency' => $this->currency,
            'type' => $this->type,
            'description' => $this->description,
            'purchasable_type' => $this->purchasable_type,
            'purchasable_id' => $this->purchasable_id,
            'metadata' => $this->metadata,
            'settled_at' => $this->settled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
