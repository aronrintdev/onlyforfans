<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class PostTypeEnum extends SmartEnum implements Selectable {

    const FREE       = 'free';
    const PRICED     = 'price'; // priced: one-time-purchaseable, as opposed to subscribeable
    const SUBSCRIBER = 'paid'; // RHS is what get set in DB so must be backwards-compatible

    public static $keymap = [
        self::FREE          => 'Free',
        self::PRICED        => 'Requires 1-time Payment',
        self::SUBSCRIBER    => 'Timeline Subscribers Only', // ie premium followers
    ];

}
