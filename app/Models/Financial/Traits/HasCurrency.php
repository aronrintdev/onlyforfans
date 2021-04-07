<?php

namespace App\Models\Financial\Traits;

use Money\Money;
use Money\Currency;
use Money\Currencies\ISOCurrencies;
use Illuminate\Support\Facades\Config;
use Money\Exception\UnknownCurrencyException;
use App\Models\Financial\Exceptions\CurrencyMismatchException;
use App\Models\Financial\Exceptions\CurrencyNotAllowedException;

trait HasCurrency
{
    /**
     * Casts a value to Money object
     *
     * @param mixed $amount
     * @return Money
     */
    public function asMoney($amount): Money
    {
        if ($amount instanceof Money) {
            return $amount;
        }
        return new Money($amount, new Currency($this->currency));
    }

    /**
     * Alias for asMoney.
     * Casts a value to Money object.
     *
     * @param mixed $item
     * @return Money
     */
    public function castToMoney($item): Money
    {
        return $this->asMoney($item);
    }

    /**
     * Currency being worked with.
     * This is an `ISO 4217`, a three letter currency code.
     * For more information: https://en.wikipedia.org/wiki/ISO_4217
     */
    public function getCurrencyAttribute()
    {
        if (!isset($this->attributes['currency'])) {
            $this->attributes['currency'] = Config::get("transactions.systems.{$this->getSystem()}.defaults.currency");
        }
        return $this->attributes['currency'];
    }

    /**
     * Validates Currency Attribute to make sure it is allowed on this system
     *
     * @return void
     */
    public function setCurrencyAttribute($value)
    {
        if ($value instanceof Currency) {
            $value = $value->getCode();
        }
        $allowedCurrencies = $this->getAllowedCurrencies();
        if (isset($allowedCurrencies)) {
            if (!isset($allowedCurrencies[$value])) {
                throw new CurrencyNotAllowedException($value, $this->getSystem());
            }
        } else {
            // Use default ISO currencies
            if (!(new ISOCurrencies())->contains(new Currency($value))) {
                throw new UnknownCurrencyException('Cannot find ISO currency ' . $value);
            }
        }
        $this->attributes['currency'] = $value;
    }

    /**
     * Gets allowed currencies in this system
     *
     * @return array|null
     */
    public function getAllowedCurrencies()
    {
        return Config::get("transactions.systems.{$this->getSystem()}.availableCurrencies", null);
    }

    /**
     * Gets default system
     *
     * @return string
     */
    public function getSystem(): string
    {
        if (isset($this->system)) {
            return $this->system;
        }
        return Config::get("transactions.default");
    }

    public static function getDefaultCurrency(): string
    {
        $defaultSystem = Config::get("transactions.default");
        return Config::get("transactions.systems.{$defaultSystem}.defaults.currency");
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