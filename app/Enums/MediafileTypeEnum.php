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

    public static function getSubfolder($mftype) {
        switch ($mftype) {
        case MediafileTypeEnum::VAULT:
            return 'vaultfolders';
        case MediafileTypeEnum::STORY:
            return 'stories';
        case MediafileTypeEnum::POST:
            return 'posts';
        default:
            return 'default';
        }
    }
}
