<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class ShareableAccessLevelEnum extends SmartEnum implements Selectable {

    const DEFAULT = 'default';
    const PREMIUM = 'premium';
    const REVOKED = 'revoked';

    public static $keymap = [
        self::DEFAULT => 'Default',
        self::PREMIUM => 'Premium',
        self::REVOKED => 'Revoked',
    ];

}
