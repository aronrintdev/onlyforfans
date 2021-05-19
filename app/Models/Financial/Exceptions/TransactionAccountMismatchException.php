<?php

namespace App\Models\Financial\Exceptions;

use App\Models\Financial\Account;
use App\Models\Financial\Transaction;
use RuntimeException;

class TransactionAccountMismatchException extends RuntimeException
{
    /**
     * @var Account
     */
    protected $account;
    /**
     * @var Transaction
     */
    protected $transaction;

    public function __construct(Account $account, Transaction $transaction)
    {
        $this->make($account, $transaction);
    }

    public function make(Account $account, Transaction $transaction)
    {
        $this->account = $account;
        $this->transaction = $transaction;

        $this->message = ("Transaction [{$transaction->getKey()}] belongs to Account [$transaction->account_id], not to Account [{$account->getKey()}]");
    }
}