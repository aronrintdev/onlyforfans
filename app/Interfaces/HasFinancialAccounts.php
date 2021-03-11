<?php

namespace App\Interfaces;

use App\Models\Financial\Account;

interface HasFinancialAccounts
{
    /**
     * Get the internal financial account belonging to this model
     */
    public function getInternalAccount(string $system, string $currency): Account;
    /**
     * Create the internal financial account belonging to this model
     */
    public function createInternalAccount(string $system, string $currency): Account;

}