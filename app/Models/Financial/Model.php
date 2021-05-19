<?php

namespace App\Models\Financial;

use App\Models\Model as ModelsModel;
use Illuminate\Support\Facades\Config;

/**
 * Financial System Base Model
 */
class Model extends ModelsModel
{

    protected $connection = 'financial';

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
