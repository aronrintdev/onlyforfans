<?php

namespace App\Interfaces;

/**
 * @package App\Interfaces
 */
interface Subscribable extends PaymentSendable, Shareable, CanFormatMoney
{
    /**
     * Verifies if a price point if valid for purchasing this model
     *
     * @param int|Money $amount
     * @return bool
     */
    public function verifyPrice($amount): bool;

    public function getPrimaryOwner();
}
