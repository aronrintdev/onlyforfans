<?php

namespace App\Apis\Segpay\Enums;

use App\Enums\SmartEnum;

class TransactionType extends SmartEnum
{
    /** Sale */
    const SALE   = 'sale';
    /** Chargeback */
    const CHARGE = 'charge';
    /** Refund */
    const CREDIT = 'credit';

    public static $keymap = [
        self::SALE   => 'Sale',
        self::CHARGE => 'Chargeback',
        self::CREDIT => 'Refund',
    ];
}