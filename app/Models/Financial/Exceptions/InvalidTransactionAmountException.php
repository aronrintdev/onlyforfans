<?php

namespace App\Models\Financial\Exceptions;

use Illuminate\Support\Facades\Config;
use RuntimeException;

class InvalidTransactionAmountException extends RuntimeException
{
    protected $amount;
    protected $model;

    public function __construct($amount, $model = null)
    {
        $this->make($amount, $model);
    }

    public function make($amount, $model)
    {
        $this->amount = $amount;
        $this->model = $model;

        $this->message = "Invalid Amount Specified for Transaction: [{$amount}].";
    }
}
