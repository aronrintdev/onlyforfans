<?php

namespace App\Models\Financial\Exceptions;

use RuntimeException;
use Illuminate\Support\Arr;

class FeesTooHighException extends RuntimeException
{
    protected $system;
    protected $fees;
    protected $transaction;
    protected $totalTaken;

    public function __construct($system, $fees, $transaction, $totalTaken)
    {
        $this->make($system, $fees, $transaction, $totalTaken);
    }

    public function make($system, $fees, $transaction, $totalTaken)
    {
        $this->system = $system;
        $this->fees = $fees;
        $this->transaction = $transaction;
        $this->totalTaken = $totalTaken;

        $this->message = "The fees in system [{$system}] for transaction [{$transaction->id}] take 100% or more of a transaction. Fees take [{$totalTaken}] of available [{$transaction->credit_amount->getAmount()}]";
        dump($transaction);
    }
}
