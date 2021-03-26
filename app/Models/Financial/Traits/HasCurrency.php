<?php

namespace App\Models\Financial\Traits;

use Money\Money;
use Money\Currency;
use Illuminate\Support\Facades\Config;
use App\Models\Financial\Exceptions\CurrencyMismatchException;

trait HasCurrency
{
    public function asMoney($amount): Money
    {
        if ($amount instanceof Money) {
            return $amount;
        }
        return new Money($amount, new Currency($this->currency));
    }


    /**
     * Currency being worked with.
     * This is an `ISO 4217`, a three letter currency code.
     * For more information: https://en.wikipedia.org/wiki/ISO_4217
     */
    public function getCurrencyAttribute()
    {
        if (!isset($this->attributes['currency'])) {
            $this->attributes['currency'] = Config::get('transactions.systems.' . $this->system . '.defaults.currency');
        }
        return $this->attributes['currency'];
    }

    /* ----------------------- Verification Functions ----------------------- */
    #region Verification

    /**
     * Checks that two models have the same currency
     *
     * @param  mixed  $model
     * @return  bool
     */
    public function isSameCurrency($model): bool
    {
        return $this->currency === $model->currency;
    }

    /**
     * Verifies that two models have the same currency
     *
     * @param  mixed  $model
     * @throws CurrencyMismatchException
     */
    public function verifySameCurrency($model)
    {
        if (!$this->isSameCurrency($model)) {
            throw new CurrencyMismatchException($this, $model);
        }
    }

    #endregion
}