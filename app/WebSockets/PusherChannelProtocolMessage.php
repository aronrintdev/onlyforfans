<?php

namespace App\Websockets;

use BeyondCode\LaravelWebSockets\WebSockets\Messages\PusherChannelProtocolMessage as ParentClass;

class PusherChannelProtocolMessage extends ParentClass {

    public function respond()
    {
        parent::respond();
        // If need additional functionality
    }

}