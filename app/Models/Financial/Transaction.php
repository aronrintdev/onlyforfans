<?php

namespace App\Models\Financial;

use App\Models\Traits\UsesUuid;

class Transaction extends Model
{
    use UsesUuid;

    protected $table = 'financial_transactions';

    protected $guarded = [
        'settled_at',
        'failed_at',
    ];

    protected $dates = [
        'settled_at',
        'failed_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /* ---------------------------- Relationships --------------------------- */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // public function resource()
    // {
    //     return $this->morphTo();
    // }

    public function reference()
    {
        return $this->hasOne(Transaction::class, 'reference_id');
    }


}