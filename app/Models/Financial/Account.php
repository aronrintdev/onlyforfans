<?php

namespace App\Models\Financial;

use App\Interfaces\Ownable;
use App\Models\Traits\OwnableTraits;
use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class Account extends Model implements Ownable
{
    use OwnableTraits, UsesUuid;

    protected $table = 'financial_accounts';

    protected $dates = [
        'balance_last_updated_at',
        'pending_last_updated_at',
        'hidden_at',
    ];

    protected static function booted()
    {
        static::creating(function (self $model): void {
            if (!isset($model->system)) {
                $model->system = Config::get('transaction.default', '');
            }
        });
    }


    /* ------------------------------ Relations ----------------------------- */
    /**
     * Owner of account
     */
    public function owner()
    {
        return $this->morphTo();
    }



    /* ------------------------------- Ownable ------------------------------ */
    public function getOwner(): Collection
    {
        return new Collection([ $this->owner ]);
    }


}
