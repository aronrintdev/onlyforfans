<?php
namespace App\Enums;

class WebhookStatusEnum extends SmartEnum {
    const UNHANDLED    = 'unhandled';
    const HANDLED      = 'handled';
    const IGNORED      = 'ignored';
    const ERROR        = 'error';

    public static $keymap = [
        self::UNHANDLED    => 'Unhandled',
        self::HANDLED      => 'Handled',
        self::IGNORED      => 'Ignored',
        self::ERROR        => 'Error',
    ];
}
