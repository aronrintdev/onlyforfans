<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class CountryTypeEnum extends SmartEnum implements Selectable {

    const US       = 'us';
    const CANADA     = 'canada';

    public static $keymap = [
        self::US      => 'United States',
        self::CANADA    => 'Canada',
    ];

}
