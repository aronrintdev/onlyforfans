<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class InviteTypeEnum extends SmartEnum implements Selectable {

    const VAULT = 'vault';

    public static $keymap = [
        self::VAULT => 'Vault',
    ];

}
