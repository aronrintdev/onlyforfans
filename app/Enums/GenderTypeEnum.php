<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class GenderTypeEnum extends SmartEnum implements Selectable {

    const MALE       = 'male';
    const FEMALE     = 'female';
    const OTHER      = 'other';

    public static $keymap = [
        self::MALE      => 'Male',
        self::FEMALE    => 'Female',
        self::OTHER     => 'Other',
    ];

}
