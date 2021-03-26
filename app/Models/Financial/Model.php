<?php

namespace App\Models\Financial;

use App\Models\Financial\Exceptions\CurrencyMismatchException;
use App\Models\Financial\Exceptions\InvalidFinancialSystemException;
use App\Models\Model as ModelsModel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Money\Currency;
use Money\Money;

/**
 * Financial System Base Model
 */
class Model extends ModelsModel
{
    protected $minTransactions = null;

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

}
