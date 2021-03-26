<?php

namespace App\Models\Financial\Exceptions;

use RuntimeException;
use App\Interfaces\Purchaseable;
use App\Models\Financial\Account;
use Illuminate\Support\Facades\Config;

class InvalidPaymentAmountException extends RuntimeException
{
    protected $account;
    protected $amount;
    protected $purchasable;

    public function __construct(Account $account, $amount, Purchaseable $purchasable)
    {
        $this->make($account, $amount, $purchasable);
    }

    public function make(Account $account, $amount, Purchaseable $purchasable)
    {
        $this->account = $account;
        $this->amount = $amount;
        $this->purchasable = $purchasable;

        $this->message = "Invalid amount specified for purchase [{$amount->getAmount()}]. Item priced at [{$purchasable->price->getAmount()}]";
    }
}
