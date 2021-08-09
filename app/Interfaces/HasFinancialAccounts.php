<?php

namespace App\Interfaces;

use App\Models\Financial\Account;

interface HasFinancialAccounts
{
    /**
     * Get the Wallet Internal account of this owner
     *
     * @param string $system   Name of financial system
     * @param string $currency Currency of financial system
     * @return Account
     */
    public function getWalletAccount(string $system, string $currency): Account;

    /**
     * Create the Wallet Internal account of this owner
     *
     * @param string $system   Name of financial system
     * @param string $currency Currency of financial system
     * @return Account
     */
    public function createWalletAccount(string $system, string $currency): Account;

    /**
     * Get the Earnings Internal account of this owner
     *
     * @param string $system   Name of financial system
     * @param string $currency Currency of financial system
     * @return Account
     */
    public function getEarningsAccount(string $system, string $currency): Account;

    /**
     * Create the Wallet Internal account of this owner
     *
     * @param string $system   Name of financial system
     * @param string $currency Currency of financial system
     * @return Account
     */
    public function createEarningsAccount(string $system, string $currency): Account;

}