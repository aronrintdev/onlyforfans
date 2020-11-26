<?php
namespace App\Enums;

use App\Interfaces\Selectable;

// CMS Content Type
class StoryTypeEnum extends SmartEnum implements Selectable {

    const PHOTO    = 'photo';
    const TEXT     = 'text';

    public static $keymap = [
        self::PHOTO    => 'Photo',
        self::TEXT     => 'Text',
    ];

}
