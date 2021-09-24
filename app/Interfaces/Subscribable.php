<?php

namespace App\Interfaces;

use Money\Money;
use App\Models\SubscriptionPrice;
use App\Enums\SubscriptionPeriodEnum;

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
    public function verifyPrice($amount, string $period = SubscriptionPeriodEnum::DAILY, int $period_interval = 30): bool;

    public function getPrimaryOwner();

    public function setPrice(Money $amount, string $period = SubscriptionPeriodEnum::DAILY, int $period_interval = 30): SubscriptionPrice;
}
