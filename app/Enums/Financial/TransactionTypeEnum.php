<?php

namespace App\Enums\Financial;

use App\Enums\SmartEnum;

class TransactionTypeEnum extends SmartEnum
{
    const CHARGEBACK         = 'chargeback';
    const CHARGEBACK_PARTIAL = 'chargeback_partial';
    const CREDIT             = 'refund';
    const FEE                = 'fee';
    const PAYMENT            = 'payment';
    const PAYOUT             = 'payout';
    const SALE               = 'sale';
    const SUBSCRIPTION       = 'subscription';
    const TIP                = 'tip';

    public static $keymap = [
        self::CHARGEBACK         => 'Chargeback',
        self::CHARGEBACK_PARTIAL => 'Chargeback Partial',
        self::CREDIT             => 'Refund',
        self::FEE                => 'Fee',
        self::PAYMENT            => 'Payment',
        self::PAYOUT             => 'Payout',
        self::SALE               => 'Sale',
        self::SUBSCRIPTION       => 'Subscription',
        self::TIP                => 'Tip',
    ];
}
