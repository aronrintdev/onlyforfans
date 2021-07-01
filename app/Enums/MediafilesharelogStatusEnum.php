<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class MediafilesharelogStatusEnum extends SmartEnum implements Selectable {

    const PENDING = 'pending';
    const APPROVED = 'approved';
    const DECLINED = 'declined';

    public static $keymap = [
        self::PENDING => 'Pending',
        self::APPROVED => 'Approved',
        self::DECLINED => 'Declined',
    ];

}

