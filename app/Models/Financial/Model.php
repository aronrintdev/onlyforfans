<?php

namespace App\Models\Financial;

use App\Models\Model as ModelsModel;
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
        if (!isset($this->system)) {
            $this->system = Config::get('transactions.default');
        }
        return $this->system;
    }

    /**
     * Currency being worked with.
     * This is an `ISO 4217`, a three letter currency code.
     * For more information: https://en.wikipedia.org/wiki/ISO_4217
     */
    public function getCurrencyAttribute()
    {
        if (!isset($this->currency)) {
            $this->currency = Config::get('transactions.systems.' . $this->system . '.defaults.currency');
        }
        return $this->currency;
    }

    /**
     * Min Transactions array
     */
    public function getMinTransactionsAttribute()
    {
        if (!isset($this->minTransactions)) {
            $this->minTransactions = Config::get('transactions.systems.' . $this->system . '.minTransactions');
        }
        return $this->minTransactions;
    }
}
