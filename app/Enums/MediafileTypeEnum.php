<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class MediafileTypeEnum extends SmartEnum implements Selectable {

    const STORY = 'story';

    public static $keymap = [
        self::STORY => 'Story',
    ];

}
