<?php

namespace App\Enums\Financial;

use App\Enums\SmartEnum;

class TransactionTypeEnum extends SmartEnum
{
    const SALE               = 'sale';
    const SUBSCRIPTION       = 'subscription';
    const CHARGEBACK         = 'chargeback';
    const CHARGEBACK_PARTIAL = 'chargeback_partial';
    const CREDIT             = 'refund';
    const PAYMENT            = 'payment';
    const TIP                = 'tip';
    const FEE                = 'fee';

    public static $keymap = [
        self::SALE               => 'Sale',
        self::SUBSCRIPTION       => 'Subscription',
        self::CHARGEBACK         => 'Chargeback',
        self::CHARGEBACK_PARTIAL => 'Chargeback Partial',
        self::CREDIT             => 'Refund',
        self::PAYMENT            => 'Payment',
        self::TIP                => 'Tip',
        self::FEE                => 'Fee',
    ];
}
