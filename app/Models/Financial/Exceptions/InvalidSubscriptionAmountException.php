<?php

namespace App\Models\Financial\Exceptions;

use RuntimeException;
use App\Interfaces\Subscribable;
use App\Models\Financial\Account;
use Illuminate\Support\Facades\Config;

class InvalidSubscriptionAmountException extends RuntimeException
{
    protected $account;
    protected $amount;
    protected $subscribable;

    public function __construct(Account $account, $amount, Subscribable $subscribable)
    {
        $this->make($account, $amount, $subscribable);
    }

    public function make(Account $account, $amount, Subscribable $subscribable)
    {
        $this->account = $account;
        $this->amount = $amount;
        $this->subscribable = $subscribable;

        $this->message = "Invalid amount specified for purchase [{$amount->getAmount()}]. Item priced at [{$subscribable->price->getAmount()}]";
    }
}
