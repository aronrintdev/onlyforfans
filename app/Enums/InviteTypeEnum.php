<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class InviteTypeEnum extends SmartEnum implements Selectable {

    const VAULT = 'vault'; // %TODO: this should probably be VAULTFOLDER

    public static $keymap = [
        self::VAULT => 'Vault',
    ];

}
