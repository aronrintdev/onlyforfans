<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class ShareableAccessLevelEnum extends SmartEnum implements Selectable {

    const DEFAULT = 'default';
    const PREMIUM = 'premium';

    public static $keymap = [
        self::DEFAULT => 'Default',
        self::PREMIUM => 'Premium',
    ];

}
