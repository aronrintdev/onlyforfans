<?php

namespace App\Models\Financial\Exceptions;

use RuntimeException;
use App\Models\Financial\Transaction;

class TransactionNotSettledException extends RuntimeException
{
    protected Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->make($transaction);
    }

    public function make(Transaction $transaction)
    {
        $this->transaction = $transaction;

        $this->message = "Transaction is not settled yet. Transaction Id [{$transaction->getKey()}].";
    }
}
