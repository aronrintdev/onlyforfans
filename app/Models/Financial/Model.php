<?php

namespace App\Models\Financial;

use App\Models\Financial\Exceptions\CurrencyMismatchException;
use App\Models\Financial\Exceptions\InvalidFinancialSystemException;
use App\Models\Model as ModelsModel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

/**
 * Financial System Base Model
 */
class Model extends ModelsModel
{
    protected $minTransactions = null;

    /**
     * System this model is working under
     */
    public function getSystemAttribute()
    {
        if (!isset($this->attributes['system'] )) {
            $this->attributes['system'] = Config::get('transactions.default');
        }
        return $this->attributes['system'];
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

    /**
     * Min Transactions array
     */
    public function getMinTransactionsAttribute()
    {
        if (!isset($this->attributes['minTransactions'])) {
            $this->attributes['minTransactions'] = Config::get('transactions.systems.' . $this->system . '.minTransactions');
        }
        return $this->attributes['minTransactions'];
    }

    /* ----------------------- Verification Functions ----------------------- */
    /**
     * Checks if a system is valid in App config
     */
    public function isSystemValid(string $system = null): bool {
        if (!isset($system)) {
            $system = $this->system;
        }
        return Arr::exists(Config::get('transactions.systems', []), $system);
    }

    /**
     * Verifies if a system is valid to be used
     *
     * @throws InvalidFinancialSystemException
     */
    public function verifySystemIsValid(string $system = null) {
        if (!$this->isSystemValid($system)) {
            throw new InvalidFinancialSystemException($system);
        }
    }

    /**
     * Checks that two models have the same currency
     */
    public function isSameCurrency($model): bool
    {
        return $this->currency !== $model->currency;
    }

    /**
     * Verifies that two models have the same currency
     *
     * @throws CurrencyMismatchException
     */
    public function verifySameCurrency($model)
    {
        if (!$this->isSameCurrency($model)) {
            throw new CurrencyMismatchException($this, $model);
        }
    }

}
