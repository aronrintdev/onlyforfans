<?php

namespace App\Interfaces;

use App\Models\Financial\Account;

/**
 * Payments are sendable to this model
 *
 * @package App\Interfaces
 */
interface PaymentSendable extends Ownable, IsModel
{

    //public function renderName() : string;

    /**
     * Gets the financial account that purchase transactions will go to
     *
     * @param string $system  Financial System
     * @param string $currency  Currency being used for transaction
     * @return Account
     */
    public function getOwnerAccount(string $system, string $currency): Account;

    /**
     * The string used in the transaction description.
     * e.i. "Purchase of `{This methods return}`"
     *
     * @return string
     */
    public function getDescriptionNameString(): string;

}
