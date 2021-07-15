<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class VerifyServiceTypeEnum extends SmartEnum implements Selectable {

    const IDMERIT    = 'idmerit';

    public static $keymap = [
        self::IDMERIT    => 'ID Merit',
    ];

}
