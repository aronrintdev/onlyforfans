<?php

namespace App\Http\Resources;

use Illuminate\Support\Collection;
use App\Models\Financial\Transaction as TransactionModel;
use Illuminate\Http\Resources\Json\JsonResource;

class Transaction extends JsonResource
{
    //

    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'credit_amount' => $this->credit_amount->getAmount(),
            'debit_amount' => $this->debit_amount->getAmount(),
            'currency' => $this->currency,
            'type' => $this->type,
            'description' => $this->description,
            'resource_type' => $this->resource_type,
            'resource_id' => $this->resource_id,
            'metadata' => $this->metadata,
            'settled_at' => $this->settled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if (isset($this->metadata['feeTransactions'])) {
            $fees = new Collection($this->metadata['feeTransactions']);
            $data['fees'] = $fees->map(function ($fee, $key) {
                return TransactionModel::find($fee['debit'])->debit_amount->getAmount();
            })->all();
        }

        $purchaser = $this->reference->account->owner;
        $data['purchaser'] = [
            'id' => $purchaser->id,
            'username' => $purchaser->username,
            'slug' => $purchaser->timeline->slug ?? '',
            'name' => $purchaser->name,
            'avatar' => $purchaser->avatar,
        ];

        return $data;
    }
}
