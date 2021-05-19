<?php

namespace App\Interfaces;

use Money\Money;

interface CanFormatMoney
{
    /**
     * Format money for current local and currency
     *
     * @param Money $money
     * @return string
     */
    public static function formatMoney(Money $money): string;

    /**
     * Format money to decimal for current local
     * @param Money $money
     * @return string
     */
    public static function formatMoneyDecimal(Money $money): string;
}