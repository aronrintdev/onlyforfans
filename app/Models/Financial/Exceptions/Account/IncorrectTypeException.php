<?php

namespace App\Models\Financial\Exceptions\Account;

use RuntimeException;

/**
 * Thrown when financial account is not expected account type
 */
class IncorrectTypeException extends RuntimeException
{
    protected $account;
    protected $expectedType;

    public function __construct($account = null, $expectedType = null)
    {
        $this->make($account, $expectedType);
    }

    public function make($account, $expectedType)
    {
        $this->account = $account;
        $this->expectedType = $expectedType;

        $this->message = "Incorrect Financial Account Type! Expected [{$expectedType}] but got [{$account->type()}]. Account id: [{$account->getKey()}]";
    }

}