<?php

namespace App\Models\Financial\Exceptions\Account;

use RuntimeException;

/**
 * Thrown when attempted transaction exceeds the balance of an account.
 */
class InsufficientFundsException extends RuntimeException
{
    protected $account;
    protected $transactionAmount;
    protected $balanceResult;

    public function __construct($account, $transactionAmount, $balanceResult)
    {
        $this->make($account, $transactionAmount, $balanceResult);
    }

    public function make($account, $transactionAmount, $balanceResult)
    {
        $this->account = $account;
        $this->transactionAmount = $transactionAmount;
        $this->balanceResult = $balanceResult;

        $this->message = "Insufficient Funds to make transaction! Account [{$account->getKey()}] with balance of [{$account->balance->getAmount()}] cannot make transaction of [{$transactionAmount}] as it would result in a balance of [{$balanceResult}]";
    }
}
