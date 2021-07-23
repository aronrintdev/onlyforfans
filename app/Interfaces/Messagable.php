<?php

namespace App\Interfaces;

/**
 * Interface for items that can be attached to a message
 * @package App\Interfaces
 */
interface Messagable
{
    public function getMessagableArray();
}
