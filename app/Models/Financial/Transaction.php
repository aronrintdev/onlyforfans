<?php

namespace App\Models\Financial;

use App\Models\Traits\UsesUuid;

class Transaction extends Model
{
    use UsesUuid;

    protected $table = 'financial_transactions';

    /* ---------------------------- Relationships --------------------------- */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account');
    }

    public function resource()
    {
        return $this->morphTo();
    }

    public function reference()
    {
        return $this->hasOne(Transaction::class, 'reference');
    }


}