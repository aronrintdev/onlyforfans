<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class ContenttagAccessLevelEnum extends SmartEnum implements Selectable {

    const OPEN         = 'open';
    const MGMTGROUP    = 'management-group';
    const OWNER        = 'owner';

    public static $keymap = [
        self::OPEN       => 'Open (Public)',
        self::MGMTGROUP  => 'Management Group Only',
        self::ONWER      => 'Owner Only',
    ];

}
