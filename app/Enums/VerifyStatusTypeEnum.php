<?php
namespace App\Enums;

use App\Interfaces\Selectable;

// Encapsulates status of a verify account request typically using a 
// 3rd party service such as ID Merit
class VerifyStatusTypeEnum extends SmartEnum implements Selectable {

    const PENDING    = 'pending';
    const VERIFIED   = 'verified';
    const REJECTED   = 'rejected';

    public static $keymap = [
        self::PENDING    => 'Pending',
        self::VERIFIED   => 'Verified',
        self::REJECTED   => 'Rejected',
    ];

}
