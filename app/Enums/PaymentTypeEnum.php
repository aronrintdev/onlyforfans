<?php
namespace App\Enums;

use App\Interfaces\Selectable;

// aka FanledgerTypeEnum
class PaymentTypeEnum extends SmartEnum implements Selectable {

    const TIP = 'tip'; // one-time
    const PURCHASE = 'purchase';
    const SUBSCRIPTION = 'subscription'; // recurring

    public static $keymap = [
        self::TIP => 'Tip',
        self::PURCHASE => 'Purchase',
        self::SUBSCRIPTION => 'Subscription',
    ];

}
