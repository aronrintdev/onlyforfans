<?php

namespace App\WebSockets;

use BeyondCode\LaravelWebSockets\WebSockets\Messages\PusherClientMessage as ParentClass;

class PusherClientMessage extends ParentClass
{
    public function respond()
    {
        parent::respond();
        // If additional functionality is needed
    }
}

