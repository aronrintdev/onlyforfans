<?php
namespace App\Enums;

use App\Interfaces\Selectable;

class MediafileTypeEnum extends SmartEnum implements Selectable {

    const STORY = 'story';
    const VAULT = 'vault';

    public static $keymap = [
        self::STORY => 'Story',
        self::VAULT => 'Vault',
    ];

}
