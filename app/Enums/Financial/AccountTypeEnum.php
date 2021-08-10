<?php

namespace App\Enums\Financial;

use App\Enums\SmartEnum;

class AccountTypeEnum extends SmartEnum
{
    /**
     * Assume infinite funds coming into system from external source with permission from provider
     * Account is an 'Entry' point for funds in a financial system
     *
     * This will always have a resource attached, such as a credit card
     */
    const IN       = 'in';

    /**
     * Assume infinite funds going out of system to external source.
     * Account is an 'Exit' for funds in a financial system.
     *
     * This will almost always have a resource attached, such as a ach account.
     */
    const OUT      = 'out';

    /**
     * Account's funds live within the system
     */
    const INTERNAL = 'internal';

    public static $keymap = [
        self::IN       => 'In Account',
        self::OUT      => 'Out Account',
        self::INTERNAL => 'Internal Account',
    ];
}
