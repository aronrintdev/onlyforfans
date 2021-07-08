<?php

namespace App\Enums;

class SubscriptionPeriodEnum extends SmartEnum
{
    /**
     * Note: Make sure const values are consistent with Carbon intervals
     */

    /** Single time payment unit */
    const SINGLE  = 'single';
    /** Unit for days */
    const DAILY   = 'day';
    /** Unit for months */
    const MONTHLY = 'month';
    /** Unit for years */
    const YEARLY  = 'year';

    public static $keymap = [
        self::SINGLE  => 'One Time',
        self::DAILY   => 'Daily',
        self::MONTHLY => 'Monthly',
        self::YEARLY  => 'Yearly',
    ];
}