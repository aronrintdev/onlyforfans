<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class MediafileTypeEnum extends SmartEnum implements Selectable {

    const AVATAR = 'avatar';
    const COVER = 'cover';
    const POST = 'post';
    const STORY = 'story';
    const VAULT = 'vault';

    public static $keymap = [
        self::AVATAR => 'Avatar',
        self::COVER => 'Cover',
        self::POST => 'Post',
        self::STORY => 'Story',
        self::VAULT => 'Vault',
    ];

}
