<?php

namespace App\Models\Financial\Exceptions\Account;

use RuntimeException;

/**
 * Thrown when attempted transaction is not allowed.
 */
class TransactionNotAllowedException extends RuntimeException
{
    protected $account;

    public function __construct($account = null)
    {
        $this->make($account);
    }

    public function make($account)
    {
        $this->account = $account;

        $this->message = "Financial Account is not allowed to make transactions! Account id: [{$account->getKey()}] | Account Name: [{$account->name}]";
    }
}
