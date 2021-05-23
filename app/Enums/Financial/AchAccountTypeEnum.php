<?php

namespace App\Enums\Financial;

use App\Enums\SmartEnum;

class AchAccountTypeEnum extends SmartEnum
{
    const INDIVIDUAL = 'individual';
    const COMPANY    = 'company';

    public static $keymap = [
        self::INDIVIDUAL => 'Individual',
        self::COMPANY    => 'Company',
    ];
}
