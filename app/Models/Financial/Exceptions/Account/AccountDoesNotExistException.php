<?php

namespace App\Models\Financial\Exceptions\Account;

use RuntimeException;

/**
 * Thrown when attempted transaction exceeds the balance of an account.
 */
class AccountDoesNotExistException extends RuntimeException
{
    protected $accountId;
    protected $message;

    public function __construct($accountId = '', $message = '')
    {
        $this->make($accountId, $message);
    }

    public function make($accountId, $message)
    {
        $this->accountId = $accountId;
        $this->message = $message;

        $this->message = "Account does not exist! Account [{$accountId}] does not exits. $message";
    }
}
