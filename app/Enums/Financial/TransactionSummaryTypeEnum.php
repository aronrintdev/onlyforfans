<?php

namespace App\Enums\Financial;

use App\Enums\SmartEnum;

class TransactionSummaryTypeEnum extends SmartEnum
{
    /** Daily Summary */
    const DAILY   = 'daily';

    /** Weekly Summary */
    const WEEKLY  = 'weekly';

    /** Monthly Summary */
    const MONTHLY = 'monthly';

    /** Yearly Summary */
    const YEARLY  = 'yearly';

    /** Custom Datetime range for this bundle */
    const CUSTOM  = 'custom';

    /**
     * Bundle Summary for account that has had a large amount of transactions since the last summary was run
     */
    const BUNDLE  = 'bundle';

    public static $keymap = [
        self::DAILY   => 'Daily',
        self::WEEKLY  => 'Weekly',
        self::MONTHLY => 'Monthly',
        self::YEARLY  => 'Yearly',
        self::CUSTOM  => 'Custom',
        self::BUNDLE  => 'Bundle'
    ];
}
