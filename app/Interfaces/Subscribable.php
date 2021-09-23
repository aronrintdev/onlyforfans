<?php

namespace App\Interfaces;

use Money\Money;
use App\Models\SubscriptionPrice;

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
    public function verifyPrice($amount, string $period, int $period_interval): bool;

    public function getPrimaryOwner();

    public function setPrice(Money $amount, string $period, int $period_interval): SubscriptionPrice;
}
