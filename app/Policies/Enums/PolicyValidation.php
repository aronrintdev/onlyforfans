<?php

namespace App\Policies\Enums;

use App\Enums\SmartEnum;

class PolicyValidation extends SmartEnum
{
    /**
     * Will continue with validation
     */
    const NEXT = 'next';
    /**
     * Will succeed all validation
     */
    const PASS = 'pass';
    /**
     * Will fail validation
     */
    const FAIL = 'fail';

    public static $keymap = [
        self::NEXT => 'next',
        self::PASS => 'pass',
        self::FAIL => 'fail'
    ];

}