<?php

namespace App\Enums\Financial;

use App\Enums\SmartEnum;

class TransactionSummaryTypeEnum extends SmartEnum
{
    const DAILY   = 'daily';
    const MONTHLY = 'monthly';
    const YEARLY  = 'yearly';
    const CUSTOM  = 'custom';
    const BUNDLE  = 'bundle';

    public static $keymap = [
        self::DAILY   => 'Daily',
        self::MONTHLY => 'Monthly',
        self::YEARLY  => 'Yearly',
        self::CUSTOM  => 'Custom',
        self::BUNDLE  => 'Bundle'
    ];
}
