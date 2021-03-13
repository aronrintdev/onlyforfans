<?php

namespace App\Models\Financial\Exceptions;

use Illuminate\Support\Facades\Config;
use RuntimeException;

class TransactionAlreadySettled extends RuntimeException
{
    protected $transaction;

    public function __construct($transaction)
    {
        $this->make($transaction);
    }

    public function make($transaction)
    {
        $this->transaction = $transaction;

        if (isset($transaction->settled_at)) {
            $this->message = "Transaction attempting to be settled was already settled at [{$transaction->settled_at}].";
        } else if (isset($this->metadata) && isset($this->metadata['feeTransactions'])) {
            $this->message = "Transaction fees have already been settled for transaction.";
        } else {
            $this->message = "Transaction was already settled.";
        }
    }
}
