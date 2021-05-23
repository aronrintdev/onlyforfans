<?php

namespace App\Enums\Financial;

use App\Enums\SmartEnum;

class AchAccountBankTypeEnum extends SmartEnum
{
    const CHECKING = 'CHK';
    const SAVINGS  = 'SAV';

    public static $keymap = [
        self::CHECKING => 'Checking',
        self::SAVINGS  => 'Savings',
    ];
}
