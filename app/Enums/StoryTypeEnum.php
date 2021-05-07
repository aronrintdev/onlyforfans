<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class StoryTypeEnum extends SmartEnum implements Selectable {

    //const PHOTO    = 'photo';
    const PHOTO    = 'image'; // %FIXME: temporary workaround until DB is re-seeded
    const VIDEO    = 'video';
    const TEXT     = 'text';

    public static $keymap = [
        self::PHOTO    => 'Photo',
        self::VIDEO    => 'Video',
        self::TEXT     => 'Text',
    ];

}
