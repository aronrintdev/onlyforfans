<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class CfstatusTypeEnum extends SmartEnum implements Selectable {

    const PENDING      = 'pending';
    const REVIEW       = 'review';
    const DISCARDED    = 'discarded';
    const CONFIRMED    = 'confirmed';

    public static $keymap = [
        self::PENDING    => 'Pending',
        self::REVIEW     => 'Under Review',
        self::DISCARDED  => 'Discarded',
        self::CONFIRMED  => 'Donfirmed',
    ];

}
