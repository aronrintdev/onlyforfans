<?php

namespace App\Apis\Segpay\Enums;

use App\Enums\SmartEnum;

/**
 * Options from the stage field of SegPay Webhooks
 */
class Stage extends SmartEnum
{
    /**
     * First transaction of this type.
     */
    const INITIAL           = 'initial';
    /**
     * First rebill of a subscription.
     */
    const CONVERSION        = 'conversion';
    /**
     * Subsequent rebill transactions after a conversion.
     */
    const REBILL            = 'rebill';
    /**
     * Consumer has converted prior to original conversion date.
     */
    const INSTANTCONVERSION = 'instantconversion';

    public static $keymap = [
        self::INITIAL           => 'Initial',
        self::CONVERSION        => 'Conversion',
        self::REBILL            => 'Rebill',
        self::INSTANTCONVERSION => 'Instant Conversion',
    ];
}
