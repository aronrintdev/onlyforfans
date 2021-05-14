<?php

namespace App\Apis\Segpay\Enums;

use App\Enums\SmartEnum;

/**
 * Options from the action field of SegPay Webhooks
 */
class Action extends SmartEnum
{
    /**
     * A username and/or password was collected during payment. Segpay is making sure the username is available in your
     * system. If it isn’t, we will assign the consumer's email address as the username.
     */
    const PROBE        = 'probe';
    /**
     * Access to your system has been granted, following a purchase.
     */
    const ENABLE       = 'enable';
    /**
     * Access to your system has been removed, following an account cancellation/expiration.
     */
    const DISABLE      = 'disable';
    /**
     * A member has requested a cancellation or a refund/cancellation.
     */
    const CANCEL       = 'cancel';
    /**
     * A cancelled or expired subscription has been reactivated.
     */
    const REACTIVATION = 'reactivation';
    /**
     * An authorization has occurred.
     */
    const AUTH         = 'auth';
    /**
     * A void has occurred.
     */
    const VOID         = 'void';

    public static $keymap = [
        self::PROBE        => 'Probe',
        self::ENABLE       => 'Enable',
        self::DISABLE      => 'Disable',
        self::CANCEL       => 'Cancel',
        self::REACTIVATION => 'Reactivation',
        self::AUTH         => 'Auth',
        self::VOID         => 'Void',
    ];
}
