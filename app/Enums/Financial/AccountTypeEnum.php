<?php

namespace App\Enums\Financial;

use App\Enums\SmartEnum;

class AccountTypeEnum extends SmartEnum
{
    const IN       = 'in';
    const OUT      = 'out';
    const INTERNAL = 'internal';

    public static $keymap = [
        self::IN       => 'In Account', // Assume infinite funds coming into system from external source with permission from provider
        self::OUT      => 'Out Account', // Assume infinite funds going out of system to external source
        self::INTERNAL => 'Internal Account', // Account funds live within the system
    ];
}
