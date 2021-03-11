<?php

namespace App\Apis\Segpay\Enums;

use App\Enums\SmartEnum;

class CardType extends SmartEnum
{
    const VISA        = 'visa';
    const MASTERCARD  = 'mastercard';
    const JCB         = 'jcb';
    const DISCOVER    = 'discover';
    const ECHECK      = 'echeck';
    const DIRECTDEBIT = 'directdebit';

    public static $keymap = [
        self::VISA        => 'Visa',
        self::MASTERCARD  => 'MasterCard',
        self::JCB         => 'JCB',
        self::DISCOVER    => 'Discover',
        self::ECHECK      => 'eCheck',
        self::DIRECTDEBIT => 'DirectDebit',
    ];
}
