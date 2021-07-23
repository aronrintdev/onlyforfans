<?php

namespace App\Models\Financial\Traits;

use Money\Money;

/**
 * Passes currency methods to account currency methods
 */
trait HasCurrencyByAccount
{
    public function getCurrencyAttribute()
    {
        return $this->account->currency;
    }


    public function getAllowedCurrencies()
    {
        return $this->account->getAllowedCurrencies();
    }

    public function castToMoney($item): Money
    {
        return $this->account->castToMoney($item);
    }

    public function getSystem(): string
    {
        return $this->account->getSystem();
    }

    public function isSameCurrency($model): bool
    {
        return $this->account->isSameCurrency($model);
    }

    public function verifySameCurrency($model)
    {
        return $this->account->verifySameCurrency($model);
    }

}