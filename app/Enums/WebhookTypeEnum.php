<?php

namespace App\Enums;

/**
 * Enums for webhook type
 *
 * @package App\Enums
 */
class WebhookTypeEnum extends SmartEnum
{
    const UNKNOWN = 'unknown';
    const PUSHER  = 'pusher';
    const SEGPAY  = 'segpay';
    const IDMERIT = 'idmerit';

    public static $keymap = [
        self::UNKNOWN => 'Unknown',
        self::PUSHER  => 'Pusher',
        self::SEGPAY  => 'SegPay',
        self::IDMERIT  => 'Id Merit',
    ];
}