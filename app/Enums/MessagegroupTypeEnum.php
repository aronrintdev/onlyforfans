<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class MessagegroupTypeEnum extends SmartEnum implements Selectable {

    const MASSMSG       = 'mass-message';

    public static $keymap = [
        self::MASSMSG    => 'Mass Messsage',
    ];

}
