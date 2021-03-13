<?php

namespace App\Enums\Financial;

use App\Enums\SmartEnum;

class AccountTypeEnum extends SmartEnum
{
    const IN       = 'in';
    const OUT      = 'out';
    const INTERNAL = 'internal';

    public static $keymap = [
        self::IN       => 'In Account',
        self::OUT      => 'Out Account',
        self::INTERNAL => 'Internal Account',
    ];
}
