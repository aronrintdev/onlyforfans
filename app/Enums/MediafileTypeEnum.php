<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class MediafileTypeEnum extends SmartEnum implements Selectable {

    const STORY = 'story';
    const VAULT = 'vault';
    const COVER = 'cover';
    const AVATAR = 'avatar';

    public static $keymap = [
        self::STORY => 'Story',
        self::VAULT => 'Vault',
        self::COVER => 'Cover',
        self::AVATAR => 'Avatar',
    ];

}
