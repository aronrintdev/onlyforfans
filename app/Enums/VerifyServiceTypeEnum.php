<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class VerifyServiceTypeEnum extends SmartEnum implements Selectable {

    const IDMERIT    = 'idmerit';
    const MANUAL    = 'manual';

    public static $keymap = [
        self::IDMERIT    => 'ID Merit',
        self::MANUAL    => 'Manual Approval (outside of any 3rd party service)',
    ];

}
