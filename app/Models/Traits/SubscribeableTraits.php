<?php

namespace App\Models\Traits;

use App\Enums\SubscriptionPeriodEnum;
use App\Models\SubscriptionPrice;
use Money\Money;

trait SubscribeableTraits
{
    public function getOneMonthPrice(string $currency = 'USD')
    {
        return SubscriptionPrice::oneMonthPrice($this, $currency);
    }

    public function updateOneMonthPrice(Money $price)
    {
        return SubscriptionPrice::updatePrice($this, $price);
    }

    public function updateThreeMonthPrice(Money $price)
    {
        return SubscriptionPrice::updatePrice($this, $price, SubscriptionPeriodEnum::DAILY, 90);
    }

    public function updateSixMonthPrice(Money $price)
    {
        return SubscriptionPrice::updatePrice($this, $price, SubscriptionPeriodEnum::DAILY, 180);
    }

    public function updateTwelveMonthPrice(Money $price)
    {
        return SubscriptionPrice::updatePrice($this, $price, SubscriptionPeriodEnum::DAILY, 360);
    }

}