<?php

namespace App\Models\Financial;

use App\Enums\Financial\AccountTypeEnum;
use App\Interfaces\Ownable;
use App\Models\Financial\Exceptions\IncorrectAccountTypeException;
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

    public function resource()
    {
        return $this->morphTo();
    }


    /* ------------------------------ Functions ----------------------------- */
    /**
     * Move funds to this owners internal account
     * 
     * @return bool - Transaction created successfully
     */
    public function moveToInternal($amount, $resource = null, $metadata = null): bool
    {
        if ($this->type !== AccountTypeEnum::IN) {
            throw new IncorrectAccountTypeException($this, AccountTypeEnum::IN);
        }

        // Get internal account in this system and currency
        $internalAccount = $this->owner->getInternalAccount($this->system, $this->currency);

        return $this->moveTo($internalAccount, $amount, $resource, $metadata);
    }

    /**
     * Move funds from one account to another
     */
    public function moveTo($toAccount, $amount, $resource = null, $metadata = null): bool
    {
        // TODO: Check currency types between accounts

        // TODO: Check if accounts allowed to make transactions


        return false;
    }


    /* ------------------------------- Ownable ------------------------------ */
    public function getOwner(): Collection
    {
        return new Collection([ $this->owner ]);
    }


}
