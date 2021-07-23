<?php

namespace App\Enums\Financial;

use App\Enums\SmartEnum;

/**
 * Types for PayoutBatch
 *
 * @package App\Enums\Financial
 */
class PayoutBatchTypeEnum extends SmartEnum
{
    const SEGPAY_ACH = 'segpay_ach';

    public static $keymap = [
        self::SEGPAY_ACH => 'SegPay Ach',
    ];
}
